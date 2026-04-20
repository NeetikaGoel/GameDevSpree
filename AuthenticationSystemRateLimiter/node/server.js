const express = require("express"); //Node framework for HTTP server and routes
const cors = require("cors"); //Allows diff ports like as in PHP and Node

const app = express(); //Creates Express application
//Gives simple methods to define routes, easier to create web server, handle JSON and manage requests efficiently

app.use(cors({
    origin: true,
    credentials: false
})); //to enable Cors

app.use(express.json());

app.get("/", (req, res) => {
    res.json({ message: "Feedback server is running" });
});

app.post("/feedback", (req, res) => {
    const { rating } = req.body;

    const allowedRatings = ["good", "okay", "bad"];

    if (!rating || !allowedRatings.includes(rating)) {
        return res.status(400).json({
            success: false,
            message: "Invalid rating"
        });
    }

    setTimeout(() => { //async processing delay
        res.json({
            success: true,
            message: `Feedback received: ${rating}`
        });
    }, 600);
});

const PORT = 3000;
app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
});