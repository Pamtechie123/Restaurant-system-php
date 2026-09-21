const API_URL = "http://localhost:8000/api";


/*
 * Get the actual menu item ID
 * MongoDB may return the ID as a string or as { $oid: "..." }
 */
function getFoodId(food) {

    if (typeof food._id === "string") {
        return food._id;
    }

    if (food._id && food._id.$oid) {
        return food._id.$oid;
    }

    return null;
}


/*
 * Load the cart
 */
async function loadCart() {

    const container =
        document.getElementById("cart-container");

    if (!container) {
        console.error("Cart container not found.");
        return;
    }


    // Get cart from browser storage
    const cart =
        JSON.parse(localStorage.getItem("cart")) || [];


    console.log("Cart from localStorage:", cart);


    /*
     * If cart is empty
     */
    if (cart.length === 0) {

        container.innerHTML = `
            <div class="rounded-xl bg-white p-8 text-center shadow">

                <h2 class="text-xl font-bold">
                    Your cart is empty
                </h2>

                <p class="mt-2 text-gray-500">
                    Add some delicious food from our menu.
                </p>

                <a
                    href="menu.html"
                    class="mt-5 inline-block rounded-lg bg-brand px-6 py-3 font-semibold text-white"
                >
                    Browse Menu
                </a>

            </div>
        `;

        return;
    }


    try {

        /*
         * Get the latest menu from the backend
         */
        const response =
            await fetch(`${API_URL}/menu`);


        if (!response.ok) {
            throw new Error("Could not load menu");
        }


        const foods =
            await response.json();


        console.log("Foods from backend:", foods);


        let total = 0;

        let cartHTML = "";


        /*
         * Build each cart item
         */
        cart.forEach((cartItem, index) => {

            const food = foods.find(
                item =>
                    getFoodId(item) === cartItem.menuItemId
            );


            /*
             * If the food no longer exists
             */
            if (!food) {

                console.warn(
                    "Food not found:",
                    cartItem.menuItemId
                );

                return;
            }


            /*
             * Calculate subtotal
             */
            const subtotal =
                Number(food.price) *
                Number(cartItem.quantity);


            total += subtotal;


            /*
             * Build image URL
             */
            const imageUrl = food.imageUrl
                ? (
                    food.imageUrl.startsWith("http")
                        ? food.imageUrl
                        : `http://localhost:8000${food.imageUrl}`
                )
                : null;


            /*
             * Add item HTML
             */
            cartHTML += `

                <div class="border-b p-5">

                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                        <div class="flex items-center gap-4">

                            ${
                                imageUrl
                                    ? `
                                        <img
                                            src="${imageUrl}"
                                            alt="${food.name}"
                                            class="h-20 w-20 rounded-lg object-cover"
                                        >
                                    `
                                    : `
                                        <div class="flex h-20 w-20 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400">
                                            No image
                                        </div>
                                    `
                            }


                            <div>

                                <h2 class="text-lg font-bold">
                                    ${food.name}
                                </h2>

                                <p class="text-gray-500">
                                    ₦${Number(food.price).toLocaleString()} each
                                </p>

                            </div>

                        </div>


                        <div class="flex flex-wrap items-center gap-3">


                            <!-- Decrease quantity -->

                            <button
                                onclick="changeQuantity(${index}, -1)"
                                class="rounded-lg bg-gray-200 px-3 py-1 font-bold hover:bg-gray-300"
                            >
                                −
                            </button>


                            <!-- Quantity -->

                            <span class="min-w-[25px] text-center font-semibold">
                                ${cartItem.quantity}
                            </span>


                            <!-- Increase quantity -->

                            <button
                                onclick="changeQuantity(${index}, 1)"
                                class="rounded-lg bg-gray-200 px-3 py-1 font-bold hover:bg-gray-300"
                            >
                                +
                            </button>


                            <!-- Subtotal -->

                            <span class="font-bold">
                                ₦${subtotal.toLocaleString()}
                            </span>


                            <!-- Remove -->

                            <button
                                onclick="removeItem(${index})"
                                class="font-semibold text-red-500 hover:text-red-700"
                            >
                                Remove
                            </button>

                        </div>

                    </div>

                </div>

            `;
        });


        /*
         * If none of the cart items matched the menu
         */
        if (cartHTML === "") {

            container.innerHTML = `
                <div class="rounded-xl bg-white p-8 text-center shadow">

                    <h2 class="text-xl font-bold">
                        Cart items could not be matched
                    </h2>

                    <p class="mt-2 text-gray-500">
                        The menu item IDs in your cart do not match the menu data.
                    </p>

                    <button
                        onclick="clearCart()"
                        class="mt-5 rounded-lg bg-red-500 px-6 py-3 font-semibold text-white"
                    >
                        Clear Cart
                    </button>

                </div>
            `;

            return;
        }


        /*
         * Display the complete cart
         */
        container.innerHTML = `

            <div class="overflow-hidden rounded-xl bg-white shadow">

                ${cartHTML}


                <!-- Total -->

                <div class="flex items-center justify-between p-6">

                    <span class="text-lg font-bold">
                        Total
                    </span>

                    <span class="text-2xl font-bold text-brand">
                        ₦${total.toLocaleString()}
                    </span>

                </div>


                <!-- Checkout -->

                <div class="p-6 pt-0">

                    <button
                        onclick="checkout()"
                        class="w-full rounded-lg bg-brand px-6 py-4 font-bold text-white hover:opacity-90"
                    >
                        Proceed to Checkout
                    </button>

                </div>

            </div>

        `;

    } catch (error) {

        console.error("Cart error:", error);


        container.innerHTML = `
            <div class="rounded-lg bg-red-50 p-5 text-red-600">

                <p class="font-semibold">
                    Could not load your cart.
                </p>

                <p class="mt-1 text-sm">
                    Make sure the PHP backend is running.
                </p>

            </div>
        `;
    }
}


/*
 * Change quantity
 *
 * amount = 1  → increase
 * amount = -1 → decrease
 */
function changeQuantity(index, amount) {

    let cart =
        JSON.parse(localStorage.getItem("cart")) || [];


    if (!cart[index]) {
        return;
    }


    cart[index].quantity += amount;


    /*
     * If quantity reaches zero,
     * remove the item from the cart.
     */
    if (cart[index].quantity <= 0) {

        cart.splice(index, 1);

    }


    localStorage.setItem(
        "cart",
        JSON.stringify(cart)
    );


    /*
     * Refresh the cart display
     */
    loadCart();
}


/*
 * Remove an item completely
 */
function removeItem(index) {

    let cart =
        JSON.parse(localStorage.getItem("cart")) || [];


    cart.splice(index, 1);


    localStorage.setItem(
        "cart",
        JSON.stringify(cart)
    );


    /*
     * Refresh the cart
     */
    loadCart();
}


/*
 * Clear the entire cart
 */
function clearCart() {

    localStorage.removeItem("cart");

    loadCart();
}


/*
 * Proceed to checkout
 */
function checkout() {

    /*
     * Check if the customer is logged in
     */
    const token =
        localStorage.getItem("token");


    if (!token) {

        alert("Please login before checking out.");

        window.location.href = "login.html";

        return;
    }


    /*
     * Make sure the cart still contains items
     */
    const cart =
        JSON.parse(localStorage.getItem("cart")) || [];


    if (cart.length === 0) {

        alert("Your cart is empty.");

        return;
    }


    /*
     * Send the customer to checkout
     */
    window.location.href = "checkout.html";
}


/*
 * Load the cart when the page opens
 */
document.addEventListener(
    "DOMContentLoaded",
    loadCart
);