
//WE NEED TO DEFINE THE BLUEPRINT OF 1 PRODUCT HISTORY ROW 
// SINCE TS WE CAN USE INTERFACES

interface HistoryItem 
{
  order_id: string;
  full_name: string;
  email: string;
  phone: string;
  location: string;
  payment_method: string;
  total_amount: string;
  order_date: string;
}


// WILL TAKE PURCHASE ORDER HISSTORY FROM THE DATABASE AND SHOW ON FRONTEND
document.addEventListener("DOMContentLoaded", async () => 
{
  //GET TABLE BODY ELEMENT SO ROWS CAN GET INSERTED IN THAT TABLE

  const tbody = document.getElementById("historyTableBody") as HTMLElement | null;

  //NO TABLE BODY FOUND-STOP
  if (!tbody) return;

  try 
  {
    //FETCH PRODUCT HISTORY FROM BACKEND
    const response = await fetch("php/get_product_history.php");
    const data: HistoryItem[] = await response.json();

    //EMPTY LIST NOW WHAT??? JUST RETURN 
    if (!Array.isArray(data) || data.length === 0) 
      {
        tbody.innerHTML = `<tr><td colspan="8">No purchase history found.</td></tr>`;
        return;
      }

    tbody.innerHTML = data.map((item) => `
      <tr>
        <td>${item.order_id}</td>
        <td>${item.full_name}</td>
        <td>${item.email}</td>
        <td>${item.phone}</td>
        <td>${item.location}</td>
        <td>${item.payment_method}</td>
        <td>$${item.total_amount}</td>
        <td>${item.order_date}</td>
      </tr>
    `).join("");
  } 
  
  catch (error) 
  {
    //NO HISTORY IS THERE THEN WHAT????
    tbody.innerHTML = `<tr><td colspan="8">Failed to load purchase history.</td></tr>`;
    console.error(error);
  }

}
);