
//BLUEPRINT OF HOW PRODUCT WILL BE THERE ON FRONTEND
interface Product 
{
  id: number;
  name: string;
  category: string;
  price: number;
  rarity: string;
  quantity: number;
}

//
class GameShopApp 
{
  private cart: Product[] = [];
  private cartBadge: HTMLElement | null;
  private cartButton: HTMLButtonElement | null;
  private productCards: HTMLElement[];
  private addToCartButtons: NodeListOf<HTMLButtonElement>;
  private categoryBadges: NodeListOf<HTMLElement>;

  constructor() 
  {
    this.cartBadge = document.querySelector(".game-cart-btn .badge");
    this.cartButton = document.querySelector(".game-cart-btn");
    this.productCards = Array.from(document.querySelectorAll(".game-card")) as HTMLElement[];
    this.addToCartButtons = document.querySelectorAll(".add-to-cart-btn");
    this.categoryBadges = document.querySelectorAll(".category-badge");

    this.loadCart();
    this.updateCartBadge();
    this.bindAddToCartButtons();
    this.bindCategoryFilters();
    this.bindCartButton();
  }

  private loadCart(): void 
  {
    const savedCart = localStorage.getItem("game_cart");
    if (!savedCart) return;

    try 
    {
      this.cart = JSON.parse(savedCart) as Product[];
    } 
    catch (error) 
    {
      console.error("Could not parse cart data", error);
      this.cart = [];
    }
  }

  private saveCart(): void 
  {
    localStorage.setItem("game_cart", JSON.stringify(this.cart));
  }

  private updateCartBadge(): void 
  {
    if (!this.cartBadge) return;

    const totalItems = this.cart.reduce((sum, item) => sum + item.quantity, 0);

    this.cartBadge.textContent = totalItems.toString();
  }

  private bindAddToCartButtons(): void 
  {
    this.addToCartButtons.forEach((button) => 
    {
      button.addEventListener("click", (event: MouseEvent) => 
      {
        event.preventDefault();

        const target = event.currentTarget as HTMLElement;
        const card = target.closest(".game-card") as HTMLElement | null;
        if (!card) return;

        const product: Product = 
        {
          id: Number(card.dataset.id || 0),
          name: card.dataset.name || "Unknown Product",
          category: card.dataset.category || "unknown",
          price: Number(card.dataset.price || 0),
          rarity: card.dataset.rarity || "Common",
          quantity: 1
        };

        this.addToCart(product);
        this.showToast(`${product.name} added to cart`);
      });
    });
  }

  private addToCart(product: Product): void 
  {
    const existingProduct = this.cart.find((item) => item.id === product.id);

    if (existingProduct) 
    {
      existingProduct.quantity += 1;
    } 
    else 
    {
      this.cart.push(product);
    }

    this.saveCart();
    this.updateCartBadge();
  }

  private bindCategoryFilters(): void 
  {
    this.categoryBadges.forEach((badge) => 
    {
      badge.addEventListener("click", () => 
      {
        const category = badge.dataset.category || "all";
        this.filterProducts(category);
      });
    });
  }

  private filterProducts(category: string): void 
  {
    this.productCards.forEach((card) => 
    {
      const cardCategory = card.dataset.category || "";
      const parentCol = card.closest(".col") as HTMLElement | null;
      if (!parentCol) return;

      if (category === "all" || cardCategory === category) 
      {
        parentCol.classList.remove("d-none");
      } 
      else 
      {
        parentCol.classList.add("d-none");
      }
    });
  }

  private bindCartButton(): void 
  {
    if (!this.cartButton) return;

    this.cartButton.addEventListener("click", () => 
    {
      window.location.href = "payment.html";
    });
  }

  private showToast(message: string): void 
  {
    const toast = document.createElement("div");
    toast.className = "custom-toast";
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => 
    {
      toast.classList.add("show");
    }, 50);

    setTimeout(() => 
    {
      toast.classList.remove("show");
      setTimeout(() => {
        toast.remove();
      }, 300);
    }, 2000);
  }
}

document.addEventListener("DOMContentLoaded", () => 
{
  new GameShopApp();
});