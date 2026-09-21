async function loadFeaturedFoods() {

    const container = document.getElementById("food-container");

    try {

        const foods = await getMenu();

        container.innerHTML = "";

        const featuredFoods = foods.slice(0, 4);

        featuredFoods.forEach((food) => {

            const card = document.createElement("article");

            card.className =
                "overflow-hidden rounded-2xl border bg-white shadow-sm";

            let imageContent;

            if (food.imageUrl) {

                imageContent = `
                    <img
                        src="${food.imageUrl}"
                        alt="${food.name}"
                        class="h-48 w-full object-cover"
                    >
                `;

            } else {

                imageContent = `
                    <div class="flex h-48 items-center justify-center bg-gray-100 text-gray-400">
                        No image available
                    </div>
                `;
            }


            card.innerHTML = `

                ${imageContent}

                <div class="p-5">

                    <h3 class="text-lg font-bold">
                        ${food.name}
                    </h3>

                    <p class="mt-2 text-sm text-gray-500">
                        ${food.description || "Delicious meal prepared fresh for you."}
                    </p>

                    <div class="mt-5 flex items-center justify-between">

                        <span class="text-lg font-bold text-brand">
                            ₦${Number(food.price).toLocaleString()}
                        </span>

                        <button
                            onclick="addToCart('${food._id}')"
                            class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white"
                        >
                            Add to cart
                        </button>

                    </div>

                </div>
            `;

            container.appendChild(card);
        });

    } catch (error) {

        console.error(error);

        container.innerHTML = `
            <div class="rounded-lg bg-red-50 p-5 text-red-600">
                Unable to load menu from the server.
            </div>
        `;
    }
}


function addToCart(foodId) {

    console.log("Food added:", foodId);

    alert("Food added to cart!");
}


document.addEventListener("DOMContentLoaded", () => {

    loadFeaturedFoods();

});