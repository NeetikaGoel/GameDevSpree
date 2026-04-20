"use strict";
class GameShopApp {
    constructor() {
        this.cart = [];
        this.cartBadge = document.querySelector(".game-cart-btn .badge");
        this.cartButton = document.querySelector(".game-cart-btn");
        this.productCards = Array.from(document.querySelectorAll(".game-card"));
        this.addToCartButtons = document.querySelectorAll(".add-to-cart-btn");
        this.categoryBadges = document.querySelectorAll(".category-badge");
        this.dropdownItems = document.querySelectorAll(".dropdown-item");
        this.loadCart();
        this.updateCartBadge();
        this.bindAddToCartButtons();
        this.bindCategoryFilters();
        this.bindCartButton();
    }
    loadCart() {
        const savedCart = localStorage.getItem("game_cart");
        if (!savedCart)
            return;
        try {
            this.cart = JSON.parse(savedCart);
        }
        catch (error) {
            console.error("Could not parse cart data", error);
            this.cart = [];
        }
    }
    saveCart() {
        localStorage.setItem("game_cart", JSON.stringify(this.cart));
    }
    updateCartBadge() {
        if (!this.cartBadge)
            return;
        const totalItems = this.cart.reduce((sum, item) => sum + item.quantity, 0);
        this.cartBadge.textContent = totalItems.toString();
    }
    bindAddToCartButtons() {
        this.addToCartButtons.forEach((button) => {
            button.addEventListener("click", (event) => {
                event.preventDefault();
                const target = event.currentTarget;
                const card = target.closest(".game-card");
                if (!card)
                    return;
                const product = {
                    id: Number(card.dataset.id || 0),
                    name: card.dataset.name || "Unknown Product",
                    category: card.dataset.category || "unknown",
                    price: Number(card.dataset.price || 0),
                    rarity: card.dataset.rarity || "Common",
                    image: card.dataset.image || "",
                    quantity: 1
                };
                this.addToCart(product);
                this.showToast(`${product.name} added to cart`);
            });
        });
    }
    addToCart(product) {
        const existingProduct = this.cart.find((item) => item.id === product.id);
        if (existingProduct) {
            existingProduct.quantity += 1;
        }
        else {
            this.cart.push(product);
        }
        this.saveCart();
        this.updateCartBadge();
    }
    bindCategoryFilters() {
        this.categoryBadges.forEach((badge) => {
            badge.addEventListener("click", () => {
                const category = badge.dataset.category || "all";
                this.filterProducts(category);
            });
        });
        this.dropdownItems.forEach((item) => {
            item.addEventListener("click", (event) => {
                event.preventDefault();
                const category = item.dataset.category || "all";
                this.filterProducts(category);
            });
        });
    }
    filterProducts(category) {
        this.productCards.forEach((card) => {
            const cardCategory = card.dataset.category || "";
            const parentCol = card.closest(".col");
            if (!parentCol)
                return;
            if (category === "all" || cardCategory === category) {
                parentCol.classList.remove("d-none");
            }
            else {
                parentCol.classList.add("d-none");
            }
        });
    }
    bindCartButton() {
        if (!this.cartButton)
            return;
        this.cartButton.addEventListener("click", () => {
            if (this.cart.length === 0) {
                this.showToast("Your cart is empty");
                return;
            }
            const totalItems = this.cart.reduce((sum, item) => sum + item.quantity, 0);
            const totalPrice = this.cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
            alert(`Cart Summary\n\nItems: ${totalItems}\nTotal Price: $${totalPrice.toFixed(2)}`);
        });
    }
    showToast(message) {
        const toast = document.createElement("div");
        toast.className = "custom-toast";
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.classList.add("show");
        }, 50);
        setTimeout(() => {
            toast.classList.remove("show");
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 2000);
    }
}
document.addEventListener("DOMContentLoaded", () => {
    new GameShopApp();
});
