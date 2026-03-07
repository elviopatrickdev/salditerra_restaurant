document.addEventListener("DOMContentLoaded", () => {
    const navBar = document.querySelector('.nav-bar');
    const sidebar = document.querySelector(".nav-sidebar");
    const overlay = document.querySelector(".nav-overlay");
    const sidebarOpen = document.querySelector(".sidebarOpen");
    const sidebarClose = document.querySelector(".sidebarClose");
    const profileMenu = document.getElementById("profileMenu");
    const loginMenu = document.getElementById("loginMenu");
    const dropdown = document.querySelector(".dropdown");
    const productList = document.getElementById('product-list');
    const alertContainer = document.getElementById('top-alert-container');
    const reservationForm = document.getElementById('reservationForm');
    const alertDiv = document.getElementById('reservationAlert');
    const dateInput = document.getElementById('date');

    // ========================
    // Navbar scroll effect
    // ========================
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navBar.classList.add('scrolled');
        } else {
            navBar.classList.remove('scrolled');
        }
    });

    window.addEventListener("load", () => {
        window.scrollTo({ top: 0, behavior: "auto" });
    });

    // ========================
    // Sidebar open/close
    // ========================
    sidebarOpen.addEventListener("click", () => {
        sidebar.classList.add("active");
        overlay.classList.add("active");
        document.body.classList.add("menu-open");
    });

    const closeMenu = () => {
        sidebar.classList.remove("active");
        overlay.classList.remove("active");
        document.body.classList.remove("menu-open");
    };

    sidebarClose.addEventListener("click", closeMenu);
    overlay.addEventListener("click", closeMenu);

    // Fechar sidebar ao clicar em qualquer link
    sidebar.addEventListener("click", (e) => {
        const link = e.target.closest("a");
        if (!link) return;

        const href = link.getAttribute("href");
        closeMenu();

        if (href && !href.startsWith("#")) {
            e.preventDefault();
            setTimeout(() => window.location.href = href, 50);
        }
    });

    // ========================
    // Smooth scroll buttons
    // ========================
    document.querySelectorAll(".scroll-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const targetId = btn.dataset.target;
            const targetEl = document.getElementById(targetId);
            if (targetEl) targetEl.scrollIntoView({ behavior: "smooth" });
        });
    });

    // ========================
    // Profile/Login dropdown
    // ========================
    if (profileMenu) {
        profileMenu.addEventListener("click", e => {
            e.stopPropagation();
            dropdown.classList.toggle("open");
        });
    }

    if (loginMenu) {
        loginMenu.addEventListener("click", e => {
            e.stopPropagation();
            dropdown.classList.toggle("open");
        });
    }

    document.addEventListener("click", () => dropdown.classList.remove("open"));

    // ========================
    // Cart count
    // ========================
    function updateCartCount() {
        fetch('../backend/cart-count.php')
            .then(res => res.json())
            .then(data => {
                document.getElementById('cart-count').textContent = data.count;
            })
            .catch(err => console.error('Erro ao atualizar o carrinho:', err));
    }

    // Atualiza contador ao carregar a página
    updateCartCount();

    // ========================
    // Load products dynamically
    // ========================
    function loadProducts() {
        fetch("../backend/get_products.php?t=" + new Date().getTime())
            .then(res => res.json())
            .then(products => {
                productList.innerHTML = "";
                if (!products || products.length === 0) {
                    productList.innerHTML = "<p>No products found.</p>";
                    return;
                }
                products.forEach(product => {
                    productList.insertAdjacentHTML("beforeend", createProductCard(product));
                });

                // Atualiza contador após carregar produtos
                updateCartCount();
            })
            .catch(err => console.error("Error loading products:", err));
    }

    // ========================
    // Cria card do produto
    // ========================
    function createProductCard(product) {

        const productId = parseInt(product.id);
        const isInCart = typeof cartItems !== "undefined" && cartItems.includes(productId);

        const buttonHTML = product.stock > 0
            ? `<button type="button"
            class="btn-add-cart add-cart btn fw-bold ${isInCart ? 'btn-added' : 'btn-warning'}"
            data-id="${productId}"
            ${isInCart ? 'disabled' : ''}>
       <i class="fa-solid ${isInCart ? 'fa-circle-check' : 'fa-circle-plus'} fs-4"></i>
       ${isInCart ? 'Added' : 'Add Cart'}
   </button>`
            : `<button type="button"
            class="btn-out btn fw-bold"
            disabled>
            <i class="fa-solid fa-xmark" fs-4></i>
       Out of Stock
   </button>`;

        return `
        <div class="menu-card-horizontal scroll-animation">
            <img src="${product.image}" alt="${product.name}" class="menu-img-horizontal">
            <div class="menu-body">
                <div class="menu-header mb-2">
                    <h2 class="menu-title">${product.name}</h2>
                    <div class="menu-lines"><span></span><span></span></div>
                    <span class="menu-price">US$ ${Number(product.price).toFixed(2)}</span>
                </div>
                <div class="menu-content">
                    <p class="menu-desc">${product.description}</p>
                    ${buttonHTML}
                </div>
            </div>
        </div>
    `;
    }

    loadProducts();

    // =========================================
    // Delegação: adicionar produtos ao carrinho
    // =========================================
    productList.addEventListener('click', e => {
        const btn = e.target.closest('.btn-add-cart');
        if (!btn) return;

        const productId = parseInt(btn.dataset.id);
        const quantity = 1;

        // Evita adicionar múltiplas vezes
        if (btn.disabled) return;

        fetch('../backend/add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `product_id=${productId}&quantity=${quantity}`
        })
            .then(res => res.json())
            .then(data => {

                if (data.status === 'redirect') {
                    window.location.href = data.redirect;
                    return;
                }

                // Mostrar alerta
                const msg = document.createElement('div');
                msg.className = `alert ${data.status === 'success' ? 'alert-warning' : 'alert-danger'}`;
                msg.textContent = data.message;
                alertContainer.appendChild(msg);
                setTimeout(() => msg.classList.add('show'), 10);
                setTimeout(() => {
                    msg.classList.remove('show');
                    setTimeout(() => msg.remove(), 300);
                }, 1000);

                // Atualiza contador do carrinho
                updateCartCount();

                // 🔹 Bloquear botão após adicionar
                if (data.status === 'success') {

                    const productId = parseInt(btn.dataset.id);

                    // Atualiza array global
                    if (typeof cartItems !== "undefined" && !cartItems.includes(productId)) {
                        cartItems.push(productId);
                    }

                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa-solid fa-circle-check fs-4"></i> Added.';
                    btn.classList.remove('btn-add-cart');
                    btn.classList.add('btn-added');
                }

            })
            .catch(err => {
                console.error('Error adding product:', err);
                const msg = document.createElement('div');
                msg.className = 'alert alert-danger';
                msg.textContent = 'Error adding product.';
                alertContainer.appendChild(msg);
                setTimeout(() => msg.remove(), 1000);
            });
    });

    // Order alert
    const alerts = document.querySelectorAll(".order-alert");

    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.add("hide");
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 1000);
    });

    // ====================================
    // Define data mínima como data current 
    // ====================================

    if (dateInput) {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');

        const localDate = `${year}-${month}-${day}`;
        dateInput.setAttribute('min', localDate);
    }

    if (reservationForm) {

        reservationForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const phone = reservationForm.phone.value.trim();
            const selectedDate = reservationForm.date.value;

            const phonePattern = /^[0-9+\-\(\)\s]+$/;

            // 🔹 Validação telefone
            if (!phonePattern.test(phone)) {
                showAlert("Invalid phone number.", false);
                reservationForm.phone.focus();
                return;
            }

            // 🔹 Validação data no JS
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const chosenDate = new Date(selectedDate);

            if (chosenDate < today) {
                showAlert("It is not allowed to select past dates.", false);
                return;
            }

            const formData = new FormData(reservationForm);

            fetch('../backend/reservation.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    showAlert(data.message, data.status === 'success');

                    if (data.status === 'success') {
                        reservationForm.reset();
                    }
                })
                .catch(error => {
                    console.error(error);
                    showAlert("Unexpected error. Try again.", false);
                });

        });
    }

    function showAlert(message, isSuccess) {
        alertDiv.textContent = message;
        alertDiv.classList.remove('d-none');
        alertDiv.classList.toggle('reservation-success', isSuccess);
        alertDiv.classList.toggle('reservation-danger', !isSuccess);

        setTimeout(() => {
            alertDiv.classList.add('d-none');
        }, 3000);
    }

});
