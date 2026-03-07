<?php
// Inicia a sessão
session_start();

// Inicializa a mensagem de sucesso
$order_success_message = null;

// Verifica se o pedido foi enviado com sucesso
if (!empty($_SESSION['order_success'])) {
    $order_success_message = "Order submitted successfully.";
    // Remove a sessão para não exibir a mensagem novamente
    unset($_SESSION['order_success']);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salditerra Restaurant</title>

    <!-- Meta Tags -->

    <meta name="description"
        content="Salditerra Restaurant in Abuja, Nigeria, offers authentic Cape Verdean cuisine with traditional dishes made from fresh ingredients and rich island flavors.">
    <meta name="keywords"
        content="Salditerra Restaurant Abuja, Cape Verdean food, Cape Verdean restaurant in Abuja, traditional Cape Verde cuisine, African cuisine Abuja, cachupa, Cape Verde food Nigeria">
    <meta name="category" content="Restaurant / Cape Verdean Cuisine">
    <meta name="author" content="Elvio Patrick">

    <!-- Custom Style Links -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <link
        href="https://fonts.googleapis.com/css2?family=Noto+Serif:wght@400;600;700&family=Noto+Sans:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="assets/favicon.png" type="image/png">

</head>

<body>

    <!--====================-->
    <!-- Cabeçalho / Navbar -->
    <!--====================-->

    <nav class="nav-bar" id="nav">

        <div class="logo d-flex align-items-center">
            <a href="index.php"><img src="assets/logo1.png" style="width: 50px;" alt="logo"></a>
            <a href="index.php"><img src="assets/logo2.png" style="width: 110px;" alt="logo"></a>
        </div>

        <div class="links nav-links">

            <ul class="d-flex">
                <li>
                    <a href="index.php"><span>HOME</span></a>
                </li>
                <li>
                    <a href="#about_us"><span>ABOUT US</span></a>
                </li>
                <li>
                    <a href="#main_course"><span>MAIN COURSE</span></a>
                </li>
                <li>
                    <a href="#menu"><span>MENU</span></a>
                </li>
                <li>
                    <a href="#contact"><span>CONTACT</span></a>
                </li>
            </ul>
        </div>

        <div class="links">

            <?php
            $cartLink = "login-register.php";

            if (
                isset($_SESSION['user_type']) &&
                ($_SESSION['user_type'] === 'admin' || $_SESSION['user_type'] === 'user')
            ) {
                $cartLink = "cart.php";
            }
            ?>

            <ul class="cart-login">
                <li>
                    <a class="nav-cart" href="<?php echo $cartLink; ?>">
                        <lord-icon
                            src="https://cdn.lordicon.com/fmsilsqx.json"
                            trigger="hover"
                            colors="primary:#ffffff"
                            style="width:16px;height:16px;">
                        </lord-icon>
                        <span id="cart-count" class="cart-badge">0</span>
                    </a>
                </li>

                <li class="sidebarOpen">
                    <i class="fa-solid fa-bars"></i>
                </li>

                <!-- Photo Profile -->

                <?php if (isset($_SESSION['username'])): ?>
                    <li class="login-profile profile-trigger" id="profileMenu">
                        <i class="fa-solid fa-angle-down"></i>
                        <?php if (isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])): ?>
                            <img src="<?php echo htmlspecialchars($_SESSION['profile_image']); ?>"
                                style="width:36px; height:36px; border-radius:50%; object-fit:cover;" alt="profile photo">
                        <?php else: ?>
                            <i class="fa-solid fa-user"></i>
                        <?php endif; ?>
                    </li>
                <?php else: ?>
                    <li class="login-profile" id="loginMenu">
                        <i class="fa-solid fa-angle-down"></i>
                        <i class="fa-solid fa-user"></i>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Dropdown do Navbar -->

    <div class="dropdown">

        <div class="dropdown-square"></div>

        <div class="dropdown-menu">
            <ul>
                <?php if (isset($_SESSION['username'])): ?>

                    <li><a href="profile.php"><i class="fa-solid fa-user-gear"></i>Profile</a></li>

                    <?php if ($_SESSION['user_type'] === 'admin'): ?>
                        <li><a href="admin.php"><i class="fa-solid fa-gears"></i>Painel Admin</a></li>
                    <?php endif; ?>

                    <li><a href="../auth/logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i>Logout</a></li>

                <?php else: ?>
                    <li><a href="login-register.php"><i class="fa-solid fa-arrow-right-to-bracket"></i>Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!--=========-->
    <!-- Sidebar -->
    <!--=========-->

    <div class="nav-sidebar">

        <div class="nav-header">

            <div class="logo-toggle">
                <a href="index.php"><img src="assets/logo1.png" style="width: 60px;" alt="logo"></a>
                <a href="index.php"><img src="assets/logo2.png" style="width: 130px;" alt="logo"></a>
            </div>

            <div class="close-sidebar ms-auto">
                <i class="fa-solid fa-xmark sidebarClose"></i>
            </div>

        </div>

        <ul class="nav-sidebar-links d-flex">
            <li>
                <a href="index.php"><span>HOME</span></a>
            </li>
            <li>
                <a href="#about_us"><span>ABOUT US</span></a>
            </li>
            <li>
                <a href="#main_course"><span>MAIN COURSE</span></a>
            </li>
            <li>
                <a href="#menu"><span>MENU</span></a>
            </li>
            <li>
                <a href="#contact"><span>CONTACT</span></a>
            </li>
        </ul>
    </div>

    <!-- Overlay -->
    <div class="nav-overlay"></div>

    <!--====================-->
    <!-- Conteúdo principal -->
    <!--====================-->

    <main>

        <!-- Mensagens de sucesso e alerta -->

        <?php if ($order_success_message): ?>
            <div class="order-alert"
                style="position: fixed; top: 90px; left: 50%; transform: translateX(-50%); 
                z-index: 1050; width: auto; max-width: 400px;">
                <?= $order_success_message ?>
            </div>
        <?php endif; ?>

        <div id="reservationAlert" class="reservation-alert d-none"
            style="position: fixed; top: 90px; left: 50%; transform: translateX(-50%); 
            z-index: 1050; width: auto; max-width: 400px;">
        </div>

        <div id="top-alert-container"
            style="position: fixed; top: 90px; left: 50%; transform: translateX(-50%); 
            z-index: 1050; width: auto; max-width: 400px;">
        </div>

        <!--=========================================-->
        <!-- Hero Section / Banner inicial da página -->
        <!--=========================================-->

        <section
            class="hero-content d-flex align-items-center justify-content-center text-center text-white position-relative"
            style="background: url('assets/hero_bg.png') center/cover no-repeat; height: 100vh;">

            <!-- Overlay para escurecer o background -->
            <div class="overlay-1"></div>

            <!-- Container central do hero -->
            <div
                class="hero-container container-fluid position-relative d-flex flex-column align-items-center justify-content-center mt-5">

                <!-- Subtítulo e separador -->
                <span class="sub-title text-warning">TASTE THE SOUL OF AFRICA</span>
                <img src="assets/separator.svg" alt="separator" class="separator" style="width:100px;">

                <!-- Título principal -->
                <h1 class=" display-1 fw-bold mb-4"><span>Cabo Verde<br>Fine Dining in<br>Abuja</span></h1>

                <!-- Descrição curta -->
                <p class="mb-3 mx-4 text-center">
                    Experience the exquisite flavors of Cabo Verde, masterfully crafted for the discerning palate.
                </p>

                <!-- Botões de ação -->
                <div class="button-bg d-flex justify-content-center m-4 position-relative">
                    <!-- Botão de reserva -->
                    <button type="button" class="scroll-btn reserve-btn" data-target="contact">
                        <i class="fa-solid fa-utensils"></i>
                        <span class="reserve-text">
                            RESERVE<br>
                            A TABLE
                        </span>
                        <span class="reserve-border"></span>
                    </button>
                </div>

                <!-- Botão para explorar o menu -->
                <div>
                    <button type="button" class="scroll-btn btn btn-outline-warning fw-bold align-text-center btn-lg mt-3 fs-6" data-target="menu">
                        Explore Our Menu
                    </button>
                </div>

            </div>
        </section>

        <!--=======================-->
        <!-- The bests Meal Mection-->
        <!--=======================-->

        <!-- Seção destacando os melhores pratos / especialidades -->
        <section class="best-content position-relative text-center">

            <!-- Overlay visual para efeito de fundo -->
            <div class="overlay-2"></div>

            <div class="container-fluid container-dark py-3 position-relative">

                <div class="row justify-content-center">
                    <div class="col-12 col-lg-7 d-flex flex-column justify-content-center align-items-center">

                        <span class="sub-title text-warning mt-5 scroll-animation">FLAVORS FIT FOR ROYALTY</span>
                        <img src="assets/separator.svg" alt="separator" class="separator scroll-animation" style="width:100px;">

                        <h2 class="fw-bold mb-4 scroll-animation">We Offer the Best</h2>

                        <p class="mb-5 mx-4 text-center scroll-animation">
                            Our dedication is to deliver the very best, blending exquisite flavors,
                            fresh ingredients, and impeccable service to make every dining experience unforgettable.
                        </p>

                        <!-- Cards de especialidades / categorias de pratos -->
                        <div class="best-cards d-flex flex-column flex-lg-row justify-content-center gap-4 mb-5">

                            <!-- Card 1: Dessert -->
                            <div class="card-item py-5 scroll-animation">
                                <img src="assets/dessert.jpg" class="pattern-img" alt="dessert">
                                <div class="card-text py-4">
                                    <h3>Dessert</h3>
                                    <a href="#menu" class="text-warning text-decoration-none">EXPLORE OUR MENU</a>
                                </div>
                            </div>

                            <!-- Card 2: Breakfast -->
                            <div class="card-item py-5 scroll-animation">
                                <img src="assets/breakfast.png" class="pattern-img" alt="breakfast">
                                <div class="card-text py-4">
                                    <h3>Breakfast</h3>
                                    <a href="#menu" class="text-warning text-decoration-none">EXPLORE OUR MENU</a>
                                </div>
                            </div>

                            <!-- Card 3: Lunch -->
                            <div class="card-item py-5 scroll-animation">
                                <img src="assets/lunch.png" class="pattern-img" alt="lunch">
                                <div class="card-text py-4">
                                    <h3>Lunch</h3>
                                    <a href="#menu" class="text-warning text-decoration-none">EXPLORE OUR MENU</a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!--=================-->
        <!-- About Us Section-->
        <!--=================-->

        <section id="about_us"
            class="container-light text-center py-5 position-relative w-100"
            style="scroll-margin-top: 30px;">

            <div class="overlay-1"></div>

            <!-- CONTAINER CENTRALIZADO -->
            <div class="d-flex flex-column flex-md-row 
                align-items-center justify-content-center 
                mx-auto"
                style="max-width: 1000px;">

                <!-- Banner -->
                <div class="d-flex justify-content-center w-100 w-md-50">

                    <div class="banner-wrapper position-relative mt-3 mx-4 scroll-animation">

                        <img src="assets/about_banner.jpg"
                            class="banner w-100"
                            alt="People enjoying a meal at Salditera Restaurant">

                        <div class="card-item-2">
                            <img src="assets/about_owner.png"
                                class="owner-img"
                                alt="Founder Salett Nogueira">
                        </div>

                    </div>

                </div>

                <!-- Container de texto -->
                <div class="w-100 w-md-50 px-3">

                    <div class="banner-text mt-5 d-flex flex-column align-items-center justify-content-center scroll-animation">

                        <span class="sub-title text-warning">OUR HISTORY</span>

                        <img src="assets/separator.svg"
                            class="separator mb-3"
                            style="width:100px;"
                            alt="decorative separator">

                        <h2 class="fw-bold mb-0">SALDITERA</h2>
                        <h3 class="fw-bold mb-4">RESTAURANT</h3>

                        <p class="mb-4 text-justify scroll-animation">
                            Salditera Restaurant is a refined gastronomic space dedicated to promoting the cuisine and
                            cultural identity of Cabo Verde and West Africa. Founded by Salett Nogueira, an ECOWAS executive
                            and gastronomy enthusiast, it offers a premium dining experience that combines tradition,
                            authenticity, and contemporary culinary excellence. Here, each visit becomes an experience of
                            flavor, comfort, and conviviality.
                        </p>

                        <p class="fw-bold mb-1 text-center scroll-animation">
                            Reservation by Phone
                        </p>

                        <p class="text-warning fs-5 text-center scroll-animation">
                            +234 999 999 9999
                        </p>

                        <button class="btn btn-outline-warning fw-bold btn-lg mt-3 fs-6 px-5 mb-4 scroll-animation"
                            data-target="contact">
                            Contact Us
                        </button>

                    </div>

                </div>

            </div>

        </section>

        <!--====================-->
        <!-- Main Course Section-->
        <!--====================-->

        <section id="main_course"
            class="container-dark text-center py-5 position-relative w-100"
            style="scroll-margin-top: 90px;">

            <div class="overlay-2"></div>

            <!-- CONTAINER CENTRALIZADO -->
            <div class="d-flex flex-column flex-md-row-reverse 
                justify-content-center align-items-center 
                mx-auto"
                style="max-width: 1000px;">

                <!-- Banner -->
                <div class="banner-wrapper position-relative mt-3 mx-4 scroll-animation">
                    <img src="assets/special_dish.png"
                        class="banner w-100 scroll-animation mb-5"
                        alt="Traditional Rich Cachupa dish">
                </div>

                <!-- Conteúdo textual -->
                <div class="position-relative d-flex flex-column align-items-center justify-content-center w-100 w-md-50 px-3">

                    <span class="sub-title text-warning scroll-animation">MAIN COURSE</span>

                    <img src="assets/separator.svg"
                        alt="separator"
                        class="separator mb-3 scroll-animation"
                        style="width:110px;">

                    <h2 class="fw-bold mb-4 scroll-animation">Rich Cachupa</h2>

                    <p class="mb-4 text-justify scroll-animation">
                        Traditional Cape Verdean cachupa, slowly prepared with corn, beans, and a rich combination of meats
                        and sausages, harmonized with fresh vegetables and authentic seasonings from the archipelago. Each
                        spoonful reveals deep layers of flavor, memory, and identity, offering a comforting and genuine
                        experience that celebrates the soul of Cape Verdean gastronomy.
                    </p>

                    <div class="d-flex flex-row justify-content-evenly align-items-center py-2 px-5 gap-1 scroll-animation">
                        <span class="sub-title text-secondary fs-6 text-decoration-line-through">
                            US$ 29,99
                        </span>
                        <span class="sub-title text-warning fs-4">
                            US$ 24,99
                        </span>
                    </div>

                    <button type="button"
                        class="scroll-btn btn btn-outline-warning fw-bold btn-lg mt-4 fs-6 px-5 scroll-animation"
                        data-target="menu">
                        Explore Our Menu
                    </button>

                </div>

            </div>
        </section>

        <!--==============-->
        <!-- Menu Section -->
        <!--=============-->

        <section id="menu" class="menu-content d-flex align-items-center justify-content-center text-center position-relative" style="scroll-margin-top: 60px;">

            <div class="overlay-2"></div>

            <div
                class="container-fluid container-light py-3 position-relative d-flex flex-column align-items-center justify-content-center">

                <span class="sub-title text-warning scroll-animation">SPECIAL SELECTION</span>
                <img src="assets/separator.svg" alt="separator" class="separator scroll-animation" style="width:100px;">
                <h2 class="fw-bold mb-5 scroll-animation">Menu Delivery</h2>

                <!-- Menu Grid -->
                <div class="menu-grid d-grid" id="product-list">

                    <!-- produtos preenchidos dinamicamente via JS -->

                </div>
                <div class="d-flex flex-column justify-content-center align-items-center text-center mt-5">
                    <p class="mb-0 scroll-animation" style="z-index: 2;">
                        Open from <span class="text-warning">9:00 AM to 11:00 PM</span>
                    </p>
                    <button type="button" class="scroll-btn btn btn-outline-warning fw-bold align-text-center btn-lg mt-4 fs-6 px-5 mb-5 scroll-animation"
                        style="z-index: 2;" data-target="menu">Explore Our
                        Menu
                    </button>
                </div>
            </div>
        </section>

        <!--====================-->
        <!-- Testimonial Section-->
        <!--====================-->

        <section class="testimonial-container text-center position-relative">

            <!-- Banner de fundo fixo -->
            <div class="banner-testimonial position-relative">
                <img src="assets/bg_ladies.png" alt="people talking" class="banner-bg">

                <!-- Overlay escura -->
                <div class="banner-overlay-2"></div>
                <div class="overlay-1"></div>
                <!-- Carrossel do conteúdo -->
                <div id="testimonialCarousel" class="carousel slide testimonial-content" data-bs-ride="carousel">
                    <div class="carousel-inner">

                        <!-- Slide 1 -->
                        <div class="carousel-item active">
                            <h3>“<br>The dishes were divine and the staff so friendly!</h3>
                            <div class="mt-4">
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                            </div>
                            <div class="testimonial-perfil my-3">
                                <img src="assets/avatar-1.png" alt="foto perfil testimonial">
                            </div>
                            <span class="sub-title text-warning fw-bold">HALIMA<br>ABDULLAHI</span>
                        </div>

                        <!-- Slide 2 -->
                        <div class="carousel-item">
                            <h3>“<br>A perfect night out with amazing food!</h3>
                            <div class="mt-4">
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                            </div>
                            <div class="testimonial-perfil my-3">
                                <img src="assets/avatar-2.png" alt="foto perfil testimonial">
                            </div>
                            <span class="sub-title text-warning fw-bold">CHINEDU<br>OKAFOR</span>
                        </div>

                        <!-- Slide 3 -->
                        <div class="carousel-item">
                            <h3>“<br>Absolutely loved the flavors and cozy atmosphere!</h3>
                            <div class="mt-4">
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                            </div>
                            <div class="testimonial-perfil my-3">
                                <img src="assets/avatar-3.png" alt="foto perfil testimonial">
                            </div>
                            <span class="sub-title text-warning fw-bold">AMINA<br>ABIOLA</span>
                        </div>

                    </div>

                    <!-- Controles -->
                    <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel"
                        data-bs-slide="prev" aria-label="Slide backward">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel"
                        data-bs-slide="next" aria-label="Slide forward">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            </div>
        </section>

        <!--====================-->
        <!-- Reservation Section-->
        <!--====================-->

        <!-- Seção de contato / reserva online -->
        <section id="contact" class="reservation-container text-center position-relative" style="scroll-margin-top: 190px;">
            <div class="container-fluid container-dark py-5">
                <div class="form-layout bg-black">

                    <!-- Formulário de reserva online -->
                    <div class="form-reservation-input">
                        <h3 class="fw-bold mb-3 scroll-animation">Online<br>Reservation</h3>
                        <p class="text-center mb-5 scroll-animation">
                            Book now by phone
                            <a href="#" class="text-warning text-decoration-none scroll-animation">+234 999 999 9999</a>
                            or by online form
                        </p>

                        <!-- Formulário que envia dados para reservation.php via POST -->
                        <form class="reservation-form scroll-animation" id="reservationForm" method="post" action="reservation.php">

                            <!-- Nome do cliente -->
                            <input type="text" name="name" placeholder="Your Name" autocomplete="name" required>

                            <!-- Número de telefone do cliente -->
                            <input type="tel" name="phone" placeholder="Your Number" autocomplete="tel" required
                                pattern="^[0-9+\-\(\)\s]+$"
                                title="Apenas números, +, -, espaços e parênteses">

                            <!-- Seleção do número de convidados -->
                            <div class="select-wrapper">
                                <label for="guest" class="sr-only">Guest</label>
                                <select id="guest" name="guest" required>
                                    <option value="" disabled selected>Nº of Guests</option>
                                    <option value="1">1 Guest</option>
                                    <option value="2">2 Guests</option>
                                    <option value="3">3 Guests</option>
                                    <option value="4">4 Guests</option>
                                    <option value="5">5 Guests</option>
                                    <option value="6">6 Guests</option>
                                    <option value="7">+7 Guests</option>
                                </select>
                            </div>

                            <!-- Data da reserva -->
                            <label for="date" class="sr-only">Date</label>
                            <input type="date" name="date" id="date" required>

                            <!-- Seleção do horário da reserva -->
                            <div class="select-wrapper">
                                <label for="hour" class="sr-only">Hour</label>
                                <select id="hour" name="hour" required>
                                    <option value="" disabled selected>Hour</option>
                                    <option value="10:00:00">10:00 AM</option>
                                    <option value="11:00:00">11:00 AM</option>
                                    <option value="12:00:00">12:00 PM</option>
                                    <option value="13:00:00">13:00 PM</option>
                                    <option value="14:00:00">14:00 PM</option>
                                    <option value="15:00:00">15:00 PM</option>
                                    <option value="16:00:00">16:00 PM</option>
                                    <option value="17:00:00">17:00 PM</option>
                                    <option value="18:00:00">18:00 PM</option>
                                    <option value="19:00:00">19:00 PM</option>
                                    <option value="20:00:00">20:00 PM</option>
                                    <option value="21:00:00">21:00 PM</option>
                                </select>
                            </div>

                            <!-- Mensagem adicional do cliente -->
                            <textarea name="message" placeholder="Message"></textarea>

                            <!-- Botão de envio do formulário -->
                            <button type="submit" id="btnSubmit" class="scroll-animation">RESERVE A TABLE</button>
                        </form>

                        <!-- Área para mensagens de erro ou confirmação -->
                        <div id="formMessage" style="color:red; margin-top:10px;"></div>
                    </div>

                    <!-- Informações de contato e horários -->
                    <div class="form-reservation-contact">
                        <h3 class="mb-5 scroll-animation">Contact Us</h3>
                        <p class="fw-bold scroll-animation">Reservation Requests</p>
                        <p class="fw-bold fs-4 text-warning scroll-animation">+234 999 999 9999</p>
                        <i class="fa-solid fa-star mb-4 text-warning scroll-animation"></i>
                        <p class="fw-bold scroll-animation">Location</p>
                        <p class="text-secondary scroll-animation">Asokoro District Abuja, Nigeria</p>
                        <p class="fw-bold scroll-animation">Brunch Hours</p>
                        <p class="text-secondary scroll-animation">Wednesday to Sunday<br>09:00 AM -16:00 PM</p>
                        <p class="fw-bold scroll-animation">Dinner Hours</p>
                        <p class="text-secondary scroll-animation">Wednesday to Sunday<br>17:00 AM -11:00 PM</p>
                    </div>
                </div>
            </div>
        </section>

        <!--==============================-->
        <!-- Reasons to choose us Section -->
        <!--==============================-->

        <!-- Seção destacando os diferenciais / motivos para escolher os serviços -->
        <section class="d-flex align-items-center justify-content-center container-dark text-center py-5 position-relative">

            <!-- Overlay visual para efeito de fundo -->
            <div class="overlay-2"></div>

            <div class="container position-relative d-flex flex-column align-items-center">

                <span class="sub-title text-warning scroll-animation">REASONS TO CHOOSE OUR SERVICES</span>

                <img src="assets/separator.svg" alt="separator" class="separator scroll-animation" style="width:100px;">

                <h2 class="fw-bold mb-5 scroll-animation">Why We Stand Out</h2>

                <!-- Cards animados -->
                <div class="row justify-content-center scroll-animation">
                    <div class="col-12 d-flex justify-content-center overflow-auto py-4">
                        <div class="animated-cards d-flex" style="z-index: 2;">

                            <!-- Card 1 -->
                            <input type="radio" name="slide" id="c1" checked>
                            <label for="c1" class="card-custom d-flex flex-column align-items-center text-white">
                                <span class="description d-flex flex-column mt-auto">
                                    <strong>Exquisite Cuisine</strong>
                                    <span>Crafted by master chefs using the finest ingredients.</span>
                                </span>
                            </label>

                            <!-- Card 2 -->
                            <input type="radio" name="slide" id="c2">
                            <label for="c2" class="card-custom d-flex align-items-center text-white">
                                <span class="description d-flex flex-column mt-auto">
                                    <strong>Elegant Ambience</strong>
                                    <span>A sophisticated setting that enhances every meal.</span>
                                </span>
                            </label>

                            <!-- Card 3 -->
                            <input type="radio" name="slide" id="c3">
                            <label for="c3" class="card-custom d-flex align-items-center text-white">
                                <span class="description d-flex flex-column mt-auto">
                                    <strong>Impeccable Service</strong>
                                    <span>Attentive staff dedicated to providing a good experience.</span>
                                </span>
                            </label>

                            <!-- Card 4 -->
                            <input type="radio" name="slide" id="c4">
                            <label for="c4" class="card-custom d-flex align-items-center text-white">
                                <span class="description d-flex flex-column mt-auto">
                                    <strong>Exclusive Experience</strong>
                                    <span>Personalized touches that make each visit memorable.</span>
                                </span>
                            </label>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!--========================-->
        <!-- Upcoming Special Event -->
        <!--========================-->

        <!-- Seção de eventos especiais -->
        <section class="d-flex flex-column justify-content-center align-items-center container-light py-3">
            <div class="event-layout mx-2 mb-5 scroll-animation">

                <!-- Card do evento -->
                <div class="card-event m-1">
                    <div class="event-content">

                        <div class="event-img">
                            <img src="assets/fifa26.png" alt="world cup fifa 2026">
                        </div>

                        <div class="event-text mx-1">
                            <span class="sub-title text-warning text-center mt-1 mb-3">Upcoming Special Event</span>
                            <h2 class="fw-bold mb-4 text-white text-center">World Cup 2026<br>Screening Gala</h2>
                            <p class="text-secondary">
                                Join us for an exclusive dining experience celebrating the spirit of football.
                                Enjoy live screenings of the 2026 World Cup matches paired with a curated menu
                                inspired by the competing nations.
                            </p>

                            <a href="#" class="mb-3">
                                VIEW EVENT DETAILS
                                <span>
                                    <lord-icon
                                        src="https://cdn.lordicon.com/zllgguxq.json"
                                        trigger="hover"
                                        state="hover-ternd-flat-3"
                                        colors="primary:#f0c040"
                                        style="width:20px;height:20px">
                                    </lord-icon>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Logos do Restaurante que patrocina o evento -->
                <div class="event-logo my-3">
                    <img src="assets/logo1.png" width="60" alt="logo1">
                    <img src="assets/logo2.png" width="140" alt="logo2">
                </div>

            </div>
        </section>
    </main>

    <!--========-->
    <!-- Footer -->
    <!--========-->

    <footer class="d-flex flex-column justify-content-center align-items-center container-light px-3"
        style="background: url('assets/footer-bg.png') 95% center/cover no-repeat;">

        <!-- WRAPPER PRINCIPAL -->
        <div class="footer-main">

            <!-- MENU CIMA -->
            <div class="footer-link footer-menu d-flex flex-column text-center">
                <a href="index.php">HOME</a>
                <a href="#about_us">ABOUT US</a>
                <a href="#main_course">MAIN COURSE</a>
                <a href="#menu">MENU</a>
                <a href="#contact">CONTACT</a>
            </div>

            <!-- CONTEÚDO CENTRAL -->
            <div class="footer-content p-5">
                <div class="footer-logo mb-4">
                    <img src="assets/logo1.png" width="60" alt="logo1">
                    <img src="assets/logo2.png" width="140" alt="logo2">
                </div>

                <p class="text-center text-secondary">Asokoro District, Abuja, Nigeria</p>
                <p class="text-center text-secondary">salditerra@gmail.com</p>
                <p class="text-center text-secondary">+234 999 999 9999</p>
                <p class="text-center text-secondary">Opening hours: 9:00 AM - 11:00 PM</p>

                <div class="my-4 text-center text-warning">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                </div>

                <h3 class="text-center">News & Exclusive Offers</h3>
                <p class="text-center text-secondary mb-5">
                    Share a photo with us and receive a discount.
                </p>

                <div class="subscribe-form d-flex justify-content-center">
                    <form class="subscribe-box">
                        <div class="input-wrapper">
                            <i class="fa-regular fa-envelope text-secondary"></i>
                            <input type="email" name="email" placeholder="Your email" autocomplete="email" required>
                        </div>
                        <button class="subscribe-btn mb-3" type="submit">SUBSCRIBE</button>
                    </form>
                </div>
            </div>

            <!-- REDES SOCIAIS BAIXO -->
            <div class="footer-link footer-social d-flex flex-column text-center">
                <a href="#">FACEBOOK</a>
                <a href="#">INSTAGRAM</a>
                <a href="#">TWITTER</a>
                <a href="#">WHATSAPP</a>
                <a href="#">LOCATION</a>
            </div>

        </div>

        <!-- DIREITOS -->
        <p class="footer-rights text-center">
            © 2026 Salditerra Restaurant. All Rights Reserved. |
            Created by <a href="#">Elvio Patrick</a>
        </p>
    </footer>

    <script>
        // Array com IDs dos produtos que já estão no carrinho
        const cartItems = <?php echo json_encode(array_keys($_SESSION['cart'] ?? [])); ?>;
    </script>

    <script src="js/scripts.js"></script>

    <script src="https://cdn.lordicon.com/lordicon.js"></script>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>