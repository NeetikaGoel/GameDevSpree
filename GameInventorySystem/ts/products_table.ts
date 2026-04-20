
//NEEDED BLUEPRINT FOR PRODUCT TABLE ROW NOW
interface ProductRow {
  id: string;
  name: string;
  category: string;
  price: string;
  rarity: string;
}

//FETCH PRODUCT TABLE DATA FROM BACKEND PHP
document.addEventListener("DOMContentLoaded", async () => 
{
  const tbody = document.getElementById("productsTableBody") as HTMLElement | null;
  if (!tbody) return;

  try 
  {
    const response = await fetch("php/get_products.php");
    const data: ProductRow[] = await response.json();

    if (!Array.isArray(data) || data.length === 0) 
      {
        tbody.innerHTML = `<tr><td colspan="5">No products found.</td></tr>`;
        return;
      }

    tbody.innerHTML = data.map((item) => `
      <tr>
        <td>${item.id}</td>
        <td>${item.name}</td>
        <td>${item.category}</td>
        <td>$${item.price}</td>
        <td>${item.rarity}</td>
      </tr>
    `).join("");
  } 
  
  catch (error) 
  
  {
    tbody.innerHTML = `<tr><td colspan="5">Failed to load products.</td></tr>`;
    console.error(error);
  }

}

);