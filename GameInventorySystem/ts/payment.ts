
// WE NEED BLUEPRINT FOR EACH PRODUCT
// TYPESCRIPT HAS INTERFACES, JS DOES NOT AT ALLLLLLL

//DEFINE ALL TYPES IN INTERFACE OF PRODUCT ONLY
interface Product 
{
  id: number;
  name: string;
  category: string;
  price: number;
  rarity: string;
  quantity: number;
}

document.addEventListener("DOMContentLoaded", () => 
{

  //READ CART DATA FROM THE LCOAL STORAGE THAT WE HAVE
  const cartSummary = document.getElementById("cartSummary") as HTMLElement | null;

  const cartDataInput = document.getElementById("cartData") as HTMLInputElement | null;

  const paymentForm = document.getElementById("paymentForm") as HTMLFormElement | null;

  const emptyCartMessage = document.getElementById("emptyCartMessage") as HTMLElement | null;

  //TYPE ANNOTATION FOR PRODUCT USE
  const cart: Product[] = JSON.parse(localStorage.getItem("game_cart") || "[]");

  if (!cartSummary || !cartDataInput || !paymentForm || !emptyCartMessage)    return;

  if (cart.length === 0) 
    {
      paymentForm.classList.add("d-none");
      emptyCartMessage.classList.remove("d-none");
      return;
    }

  let html = "<ul class='mb-2'>";
  let total = 0;
  
  //SHOW CART SUMMARY ON SCREEN - VERY IMPRESSIVE
  cart.forEach((item) => 
    {
      html += `<li>${item.name} - Qty: ${item.quantity} - $${item.price}</li>`;
      total += item.quantity * item.price;
    });

  //CART JSON FILE INTO HIDDEN INPUT FIELD
  html += `</ul><strong>Total Price: $${total.toFixed(2)}</strong>`;

  cartSummary.innerHTML = html;
  cartDataInput.value = JSON.stringify(cart);


  //ORDER SUBMIT -> REMOVE CART CONTENT NOW NO NEED OF IT
  paymentForm.addEventListener("submit", () => 
    {
      localStorage.removeItem("game_cart");
    }
  
  );
}
);