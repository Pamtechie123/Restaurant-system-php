const API_URL = "http://localhost:8000/api";


// --------------------------------------------------
// ELEMENTS
// --------------------------------------------------

const menuContainer =
    document.getElementById("menu-container");

const searchInput =
    document.getElementById("menu-search");

const menuCount =
    document.getElementById("menu-count");

const noResults =
    document.getElementById("no-results");

const categoryButtons =
    document.querySelectorAll(".category-button");


// --------------------------------------------------
// DATA
// --------------------------------------------------

let allFoods = [];

let selectedCategory = "all";


// --------------------------------------------------
// GET FOOD ID
// --------------------------------------------------

function getFoodId(food) {

    if (typeof food._id === "string") {
        return food._id;
    }

    if (food._id && food._id.$oid) {
        return food._id.$oid;
    }

    return null;
}


// --------------------------------------------------
// LOAD MENU
// --------------------------------------------------

async function loadMenu() {

    try {

        menuCount.textContent =
            "Loading...";


        const response =
            await fetch(`${API_URL}/menu`);


        if (!response.ok) {

            throw new Error(
                "Unable to load menu"
            );

        }


        const foods =
            await response.json();


        /*
         * Keep only available food.
         *
         * If your backend uses a different
         * availability field, we can adjust
         * this later.
         */

        allFoods =
            foods.filter(food => {

                if (
                    food.available === undefined &&
                    food.isAvailable === undefined
                ) {
                    return true;
                }

                return (
                    food.available === true ||
                    food.isAvailable === true
                );

            });


        displayMenu(allFoods);

    } catch (error) {

        console.error(
            "Menu error:",
            error
        );


        menuContainer.innerHTML = `

            <div class="col-span-full rounded-xl bg-white p-8 text-center">

                <p class="text-red-500">
                    Unable to load the menu.
                </p>

            </div>

        `;

        menuCount.textContent =
            "Unable to load menu";

    }

}


// --------------------------------------------------
// DISPLAY MENU
// --------------------------------------------------

function displayMenu(foods) {


    menuContainer.innerHTML = "";


    if (foods.length === 0) {

        noResults.classList.remove("hidden");

        menuCount.textContent =
            "0 meals";

        return;

    }


    noResults.classList.add("hidden");


    menuCount.textContent =
        `${foods.length} meal${foods.length === 1 ? "" : "s"}`;


    foods.forEach(food => {

        const foodId =
            getFoodId(food);


        const price =
            Number(food.price || 0);


        const image =
            food.image ||
            food.imageUrl ||
            "";


        const category =
            food.category ||
            "Meal";


        const card =
            document.createElement("div");


        card.className =
            "overflow-hidden rounded-2xl bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg";


        card.innerHTML = `

            <!-- IMAGE -->

            <div class="h-52 overflow-hidden bg-gray-100">

                ${
                    image

                    ?

                    `<img
                        src="${image}"
                        alt="${food.name || "Food"}"
                        class="h-full w-full object-cover"
                    >`

                    :

                    `<div class="flex h-full items-center justify-center text-5xl">
                        🍽️
                    </div>`
                }

            </div>


            <!-- CONTENT -->

            <div class="p-5">

                <div class="flex items-start justify-between gap-3">

                    <h3 class="text-lg font-bold">
                        ${food.name || "Unnamed Meal"}
                    </h3>

                    <span
                        class="rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-brand">
                        ${category}
                    </span>

                </div>


                <p class="mt-2 line-clamp-2 text-sm text-gray-500">

                    ${
                        food.description ||
                        "Delicious meal from ChopOps."
                    }

                </p>


                <div class="mt-5 flex items-center justify-between">

                    <span class="text-lg font-bold text-brand">
                        ₦${price.toLocaleString()}
                    </span>


                    <button
                        class="add-to-cart rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90"
                        data-id="${foodId}">

                        Add to Cart

                    </button>

                </div>

            </div>

        `;


        menuContainer.appendChild(card);

    });


    attachCartButtons();

}


// --------------------------------------------------
// SEARCH + CATEGORY FILTER
// --------------------------------------------------

function filterMenu() {

    const searchTerm =
        searchInput.value
            .trim()
            .toLowerCase();


    const filteredFoods =
        allFoods.filter(food => {


            const name =
                String(food.name || "")
                    .toLowerCase();


            const description =
                String(food.description || "")
                    .toLowerCase();


            const category =
                String(food.category || "")
                    .toLowerCase();


            const matchesSearch =
                name.includes(searchTerm) ||
                description.includes(searchTerm) ||
                category.includes(searchTerm);


            const matchesCategory =
                selectedCategory === "all" ||
                category === selectedCategory;


            return (
                matchesSearch &&
                matchesCategory
            );

        });


    displayMenu(filteredFoods);

}


// --------------------------------------------------
// SEARCH EVENT
// --------------------------------------------------

searchInput.addEventListener(
    "input",
    filterMenu
);


// --------------------------------------------------
// CATEGORY BUTTONS
// --------------------------------------------------

categoryButtons.forEach(button => {

    button.addEventListener(
        "click",
        () => {

            selectedCategory =
                button.dataset.category
                    .toLowerCase();


            categoryButtons.forEach(
                btn => {

                    btn.classList.remove(
                        "bg-brand",
                        "text-white"
                    );

                    btn.classList.add(
                        "border",
                        "border-gray-300",
                        "text-gray-700"
                    );

                }
            );


            button.classList.remove(
                "border",
                "border-gray-300",
                "text-gray-700"
            );

            button.classList.add(
                "bg-brand",
                "text-white"
            );


            filterMenu();

        }
    );

});


// --------------------------------------------------
// ADD TO CART
// --------------------------------------------------

function attachCartButtons() {

    const buttons =
        document.querySelectorAll(
            ".add-to-cart"
        );


    buttons.forEach(button => {

        button.addEventListener(
            "click",
            () => {

                const foodId =
                    button.dataset.id;


                if (!foodId) {

                    alert(
                        "Unable to add this meal to cart."
                    );

                    return;

                }


                let cart =
                    JSON.parse(
                        localStorage.getItem("cart")
                    ) || [];


                const existingItem =
                    cart.find(
                        item =>
                            item.menuItemId === foodId
                    );


                if (existingItem) {

                    existingItem.quantity += 1;

                } else {

                    cart.push({

                        menuItemId:
                            foodId,

                        quantity:
                            1

                    });

                }


                localStorage.setItem(
                    "cart",
                    JSON.stringify(cart)
                );


                button.textContent =
                    "Added ✓";


                button.classList.add(
                    "opacity-70"
                );


                setTimeout(() => {

                    button.textContent =
                        "Add to Cart";

                    button.classList.remove(
                        "opacity-70"
                    );

                }, 1000);

            }
        );

    });

}


// --------------------------------------------------
// START
// --------------------------------------------------

loadMenu();
attachCartButtons();
