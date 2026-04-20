document.addEventListener("DOMContentLoaded", function () 
{
    //wait until full html page is loaded 
    const form = document.getElementById("feedbackform"); //get form
    const responseMsg = document.getElementById("responsemsg"); //get msg
    const submitBtn = document.getElementById("submitBtn"); //get if submit button pressed

    if (!form) return; //no feedback form, stop script

    const nodeApiBase =
        document.querySelector('meta[name="node-api-base"]')?.content ||
        "http://localhost:3000";

    form.addEventListener("submit", async function (e) 
    {
        //WE CAN USE AWAIT INSIDE
        e.preventDefault(); //stops default browser form submission

        const rating = document.querySelector("select[name='rating']").value; //reading seelcted dropdown value of rating

        responseMsg.textContent = "";
        responseMsg.className = "response-message";
        submitBtn.disabled = true; //to avoid double submit
        submitBtn.textContent = "Submitting...";

        try {
            const nodeResponse = await fetch(`${nodeApiBase}/feedback`, 
                {
                    //sends feedback json to node server
                    //to async fetch requests
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ rating })
            });

            if (!nodeResponse.ok) {
                throw new Error("Node server error");
            }

            const nodeData = await nodeResponse.json();

            const phpResponse = await fetch("save_feedback.php", 
                {
                    //save in php so session can store it
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ rating })
            });

            if (!phpResponse.ok) {
                throw new Error("PHP save error"); //CHECKS FOR PHP SIDE ERROR
            }

            const phpData = await phpResponse.json();

            responseMsg.textContent = `${nodeData.message}. ${phpData.message}. Refresh dashboard to see updated welcome text.`;
            responseMsg.classList.add("success");
        } catch (error) 
        {
            responseMsg.textContent =
                "Error submitting feedback. Please make sure both PHP and Node servers are running.";
            responseMsg.classList.add("error");
            console.error(error);
        } 
        finally 
        {
            submitBtn.disabled = false;
            submitBtn.textContent = "Submit Feedback";
        }
    });
});