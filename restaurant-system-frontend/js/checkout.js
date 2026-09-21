const API_URL = "http://localhost:8000/api";


/*
|--------------------------------------------------------------------------
| Get Food ID
|--------------------------------------------------------------------------
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
|--------------------------------------------------------------------------
| Load Checkout
|--------------------------------------------------------------------------
*/
async function loadCheckout() {

    const itemsContainer =
        document.getElementById("checkout-items");

    const totalElement =
        document.getElementById("checkout-total");


    if (!itemsContainer || !totalElement) {
        console.error(
            "Checkout elements not found in HTML."
        );

        return;
    }


    const cart =
        JSON.parse(localStorage.getItem("cart")) || [];


    /*
    | Empty cart
    */
    if (cart.length === 0) {

        itemsContainer.innerHTML = `
            <div class="rounded-lg bg-gray-50 p-6 text-center">

                <p class="text-gray-500">
                    Your cart is empty.
                </p>

                <a
                    href="menu.html"
                    class="mt-4 inline-block font-semibold text-brand"
                >
                    Return to Menu
                </a>

            </div>
        `;

        totalElement.textContent = "₦0";

        return;
    }


    try {

        /*
        | Get menu from backend
        */
        const response =
            await fetch(`${API_URL}/menu`);


        if (!response.ok) {
            throw new Error(
                "Could not load menu."
            );
        }


        const foods =
            await response.json();


        let total = 0;

        let html = "";


        /*
        | Display cart items
        */
        cart.forEach(cartItem => {

            const food =
                foods.find(
                    item =>
                        getFoodId(item) ===
                        cartItem.menuItemId
                );


            if (!food) {
                return;
            }


            const subtotal =
                Number(food.price) *
                Number(cartItem.quantity);


            total += subtotal;


            html += `
                <div class="flex justify-between gap-4 border-b pb-4">

                    <div>

                        <h3 class="font-semibold">
                            ${food.name}
                        </h3>

                        <p class="text-sm text-gray-500">
                            ₦${Number(food.price).toLocaleString()}
                            ×
                            ${cartItem.quantity}
                        </p>

                    </div>


                    <span class="font-semibold">
                        ₦${subtotal.toLocaleString()}
                    </span>

                </div>
            `;
        });


        itemsContainer.innerHTML = html;


        totalElement.textContent =
            `₦${total.toLocaleString()}`;


    } catch (error) {

        console.error(
            "Checkout loading error:",
            error
        );


        itemsContainer.innerHTML = `
            <p class="text-red-500">
                Unable to load your order.
            </p>
        `;
    }
}


/*
|--------------------------------------------------------------------------
| Checkout Page
|--------------------------------------------------------------------------
|
| Wait until the HTML has completely loaded.
|
|--------------------------------------------------------------------------
*/
document.addEventListener("DOMContentLoaded", function () {


    /*
    |--------------------------------------------------------------------------
    | Get HTML Elements
    |--------------------------------------------------------------------------
    */

    const orderType =
        document.getElementById("orderType");


    const dineInContainer =
        document.getElementById("dine-in-container");


    const deliveryContainer =
        document.getElementById("delivery-container");


    const reservationId =
        document.getElementById("reservationId");


    const tableNumber =
        document.getElementById("tableNumber");


    const shippingAddress =
        document.getElementById("shippingAddress");


    const state =
        document.getElementById("state");


    const city =
        document.getElementById("city");


    const phone =
        document.getElementById("phone");


    const checkoutForm =
        document.getElementById("checkout-form");


    const message =
        document.getElementById("checkout-message");


    /*
    |--------------------------------------------------------------------------
    | Check Required Elements
    |--------------------------------------------------------------------------
    */

    if (!orderType) {

        console.error(
            "orderType element was not found."
        );

        return;
    }


    if (!checkoutForm) {

        console.error(
            "checkout-form was not found."
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Hide Optional Sections Initially
    |--------------------------------------------------------------------------
    */

    if (dineInContainer) {
        dineInContainer.classList.add("hidden");
    }


    if (deliveryContainer) {
        deliveryContainer.classList.add("hidden");
    }


    /*
    |--------------------------------------------------------------------------
    | Order Type Changed
    |--------------------------------------------------------------------------
    */

    orderType.addEventListener(
        "change",
        function () {


            /*
            | Hide both sections first
            */

            if (dineInContainer) {
                dineInContainer.classList.add("hidden");
            }


            if (deliveryContainer) {
                deliveryContainer.classList.add("hidden");
            }


            /*
            | Remove required from all optional fields
            */

            if (reservationId) {
                reservationId.required = false;
            }


            if (tableNumber) {
                tableNumber.required = false;
            }


            if (shippingAddress) {
                shippingAddress.required = false;
            }


            if (state) {
                state.required = false;
            }


            if (city) {
                city.required = false;
            }


            if (phone) {
                phone.required = false;
            }


            /*
            |--------------------------------------------------------------------------
            | DINE-IN
            |--------------------------------------------------------------------------
            */

            if (this.value === "dine-in") {

                if (dineInContainer) {
                    dineInContainer.classList.remove("hidden");
                }


                if (reservationId) {
                    reservationId.required = true;
                }


                if (tableNumber) {
                    tableNumber.required = true;
                }


                /*
                | Clear delivery fields
                */

                if (shippingAddress) {
                    shippingAddress.value = "";
                }


                if (state) {
                    state.value = "";
                }


                if (city) {
                    city.value = "";
                }


                if (phone) {
                    phone.value = "";
                }
            }


            /*
            |--------------------------------------------------------------------------
            | DELIVERY
            |--------------------------------------------------------------------------
            */

            else if (this.value === "delivery") {

                if (deliveryContainer) {
                    deliveryContainer.classList.remove("hidden");
                }


                if (shippingAddress) {
                    shippingAddress.required = true;
                }


                if (state) {
                    state.required = true;
                }


                if (city) {
                    city.required = true;
                }


                if (phone) {
                    phone.required = true;
                }


                /*
                | Clear dine-in fields
                */

                if (reservationId) {
                    reservationId.value = "";
                }


                if (tableNumber) {
                    tableNumber.value = "";
                }
            }


            /*
            |--------------------------------------------------------------------------
            | TAKEAWAY
            |--------------------------------------------------------------------------
            */

            else if (this.value === "takeaway") {

                /*
                | Clear dine-in fields
                */

                if (reservationId) {
                    reservationId.value = "";
                }


                if (tableNumber) {
                    tableNumber.value = "";
                }


                /*
                | Clear delivery fields
                */

                if (shippingAddress) {
                    shippingAddress.value = "";
                }


                if (state) {
                    state.value = "";
                }


                if (city) {
                    city.value = "";
                }


                if (phone) {
                    phone.value = "";
                }
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Place Order
    |--------------------------------------------------------------------------
    */

    checkoutForm.addEventListener(
        "submit",
        async function (event) {

            event.preventDefault();


            /*
            | Get authentication token
            */

            const token =
                localStorage.getItem("token");


            /*
            | User must be logged in
            */

            if (!token) {

                alert(
                    "Please login before placing an order."
                );


                window.location.href =
                    "login.html";


                return;
            }


            /*
            | Get cart
            */

            const cart =
                JSON.parse(
                    localStorage.getItem("cart")
                ) || [];


            if (cart.length === 0) {

                alert(
                    "Your cart is empty."
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Get Order Type
            |--------------------------------------------------------------------------
            */

            const selectedOrderType =
                orderType.value;


            /*
            |--------------------------------------------------------------------------
            | Get Dine-In Data
            |--------------------------------------------------------------------------
            */

            let selectedReservationId = null;

            let selectedTableNumber = null;


            if (
                selectedOrderType ===
                "dine-in"
            ) {

                selectedReservationId =
                    reservationId
                        ? reservationId.value.trim()
                        : null;


                selectedTableNumber =
                    tableNumber
                        ? Number(tableNumber.value)
                        : null;
            }


            /*
            |--------------------------------------------------------------------------
            | Get Delivery Data
            |--------------------------------------------------------------------------
            */

            let selectedShippingAddress = null;

            let selectedState = null;

            let selectedCity = null;

            let selectedPhone = null;


            if (
                selectedOrderType ===
                "delivery"
            ) {

                selectedShippingAddress =
                    shippingAddress
                        ? shippingAddress.value.trim()
                        : null;


                selectedState =
                    state
                        ? state.value.trim()
                        : null;


                selectedCity =
                    city
                        ? city.value.trim()
                        : null;


                selectedPhone =
                    phone
                        ? phone.value.trim()
                        : null;
            }


            /*
            |--------------------------------------------------------------------------
            | Build Order
            |--------------------------------------------------------------------------
            */

            const orderData = {

                orderType:
                    selectedOrderType,


                reservationId:
                    selectedReservationId,


                tableNumber:
                    selectedTableNumber,


                shippingAddress:
                    selectedShippingAddress,


                state:
                    selectedState,


                city:
                    selectedCity,


                phone:
                    selectedPhone,


                items:
                    cart

            };


            console.log(
                "Order being sent:",
                orderData
            );


            try {

                /*
                | Show loading message
                */

                if (message) {

                    message.textContent =
                        "Placing your order...";

                    message.className =
                        "text-center text-sm text-gray-500";
                }


                /*
                |--------------------------------------------------------------------------
                | Send order to PHP backend
                |--------------------------------------------------------------------------
                */

                const response =
                    await fetch(
                        `${API_URL}/orders`,
                        {

                            method: "POST",


                            headers: {

                                "Content-Type":
                                    "application/json",

                                "Authorization":
                                    `Bearer ${token}`

                            },


                            body:
                                JSON.stringify(
                                    orderData
                                )
                        }
                    );


                /*
                | Read backend response
                */

                const result =
                    await response.json();


                console.log(
                    "Order response:",
                    result
                );


                /*
                | Backend returned an error
                */

                if (!response.ok) {

                    throw new Error(
                        result.error ||
                        result.message ||
                        "Unable to place order."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Order Successful
                |--------------------------------------------------------------------------
                */

                localStorage.removeItem(
                    "cart"
                );


                if (message) {

                    message.textContent =
                        "Order placed successfully!";

                    message.className =
                        "text-center text-sm text-green-600";
                }


                /*
                | Go to success page
                */

                setTimeout(
                    function () {

                        window.location.href =
                            "order-success.html";

                    },
                    1000
                );


            } catch (error) {

                console.error(
                    "Checkout error:",
                    error
                );


                if (message) {

                    message.textContent =
                        error.message;

                    message.className =
                        "text-center text-sm text-red-600";
                }

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Load Checkout
    |--------------------------------------------------------------------------
    */

    loadCheckout();

});
