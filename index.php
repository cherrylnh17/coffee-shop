<!doctype html>
<html lang="en" class="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr" dir="ltr" data-pc-theme_contrast="" data-pc-theme="light">

<head>
  <title>Able Pro Tailwind Dashboard Template</title>
  <!-- [Meta] -->
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="description"
    content="Able Pro Tailwind Dashboard – a feature-rich template with a responsive design, modern UI, and features to enhance the development process." />
  <meta name="keywords"
    content="Bootstrap admin template, Dashboard UI Kit, Dashboard Template, Backend Panel, react dashboard, angular dashboard" />
  <meta name="author" content="Phoenixcoded" />

  <!-- [Favicon] icon -->
  <link rel="icon" href="assets/images/favicon.svg" type="image/x-icon" />
  <!-- [Page specific CSS] start -->
  <link href="assets/css/plugins/animate.min.css" rel="stylesheet" type="text/css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" rel="stylesheet" />
  <!-- [Page specific CSS] end -->
  <!-- [Font] Family -->
  <link rel="stylesheet" href="assets/fonts/inter/inter.css" id="main-font-link" />
  <!-- [phosphor Icons] https://phosphoricons.com/ -->
  <link rel="stylesheet" href="assets/fonts/phosphor/duotone/style.css" />
  <!-- [Tabler Icons] https://tablericons.com -->
  <link rel="stylesheet" href="assets/fonts/tabler-icons.min.css" />
  <!-- [Feather Icons] https://feathericons.com -->
  <link rel="stylesheet" href="assets/fonts/feather.css" />
  <!-- [Font Awesome Icons] https://fontawesome.com/icons -->
  <link rel="stylesheet" href="assets/fonts/fontawesome.css" />
  <!-- [Material Icons] https://fonts.google.com/icons -->
  <link rel="stylesheet" href="assets/fonts/material.css" />
  <!-- [Template CSS Files] -->
  <link rel="stylesheet" href="assets/css/style.css" id="main-style-link" />
  <script src="assets/js/tech-stack.js"></script>
  <!-- Buy Now Script -->
  <script defer src="https://fomo.codedthemes.com/pixel/CDkpF1sQ8Tt5wpMZgqRvKpQiUhpWE3bc"></script>
</head>

<body>
  <!-- [ Pre-loader ] start -->
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>
  <!-- [ Pre-loader ] End -->
  <!-- [ Header ] start -->
  <header id="home"
    class="flex items-center flex-col justify-center min-h-screen overflow-hidden relative py-[100px] bg-cover">
    <!-- [ Nav ] start -->
    <nav class="navbar bg-theme-cardbg dark:bg-themedark-cardbg bg-opacity-75 dark:bg-opacity-75 fixed top-0 z-50 w-full backdrop-blur shadow-[0_0_24px_rgba(27,46,94,.05)] dark:shadow-[0_0_24px_rgba(27,46,94,.05)]">
      <div class="container">
        <div class="static flex py-4 items-center justify-between lg:relative">
          <div class="flex flex-1 items-center justify-center lg:items-stretch lg:justify-between">
            <div class="flex flex-1 flex-shrink-0 items-center gap-2">
              <a href="#">
                <img class="h-7 w-auto" src="assets/images/logo-dark.svg" alt="Your Company" />
              </a>
              <span
                class="badge bg-success-500/10 text-success-500 rounded-full theme-version">v1.0.0</span>
            </div>
          </div>
        </div>
      </div>
    </nav>
    <!-- [ Nav ] end -->
    <div class="container">
      <div class="w-full md:w-10/12 text-center mx-auto">
        <h1 class="text-[22px] md:text-[36px] lg:text-[55px] leading-[1.2] mb-5 wow fadeInUp" data-wow-delay="0.2s">
          Selamat Datang di Website
          <span
            class="text-transparent bg-clip-text bg-gradient-to-r from-[rgb(37,161,244)] via-[rgb(249,31,169)] to-[rgb(37,161,244)] bg-[length:400%_100%] bg-left-top animate-[move-bg_24s_infinite_linear]">Träffa Coffee</span>
        </h1>
        <div class="my-5 sm:my-12 wow fadeInUp" data-wow-delay="0.4s">
          <a href="users/index.html" class="btn btn-outline-secondary mr-2 mt-1">User Page</a>
          <a href="pages/login-v1.html" class="btn btn-outline-secondary mr-2 mt-1">Login Page</a>
        </div>
        <div class="flex items-center justify-center text-center gap-6 wow fadeInUp" data-wow-delay="0.5s">
          <div class="head-rating-block">
            <div class="star mb-2">
              <i class="fas fa-star text-warning-500"></i>
              <i class="fas fa-star text-warning-500"></i>
              <i class="fas fa-star text-warning-500"></i>
              <i class="fas fa-star text-warning-500"></i>
              <i class="fas fa-star-half-alt text-warning-500"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
  <!-- [ Header ] End -->

  <!-- [ Main Content ] end -->
  <!-- Required Js -->
  <script src="assets/js/plugins/simplebar.min.js"></script>
  <script src="assets/js/plugins/popper.min.js"></script>
  <script src="assets/js/icon/custom-font.js"></script>
  <script src="assets/js/plugins/feather.min.js"></script>
  <script src="assets/js/component.js"></script>
  <script src="assets/js/theme.js"></script>
  <script src="assets/js/script.js"></script>
  <!-- [Page Specific JS] start -->
  <script src="assets/js/plugins/wow.min.js"></script>
  <script>
    // Start [ Menu hide/show on scroll ]
    let ost = 0;
    document.addEventListener('scroll', function () {
      let cOst = document.documentElement.scrollTop;
      if (cOst == 0) {
        document.querySelector('.navbar').classList.add('top-nav-collapse');
      } else if (cOst > ost) {
        document.querySelector('.navbar').classList.add('top-nav-collapse');
        document.querySelector('.navbar').classList.remove('default');
      } else {
        document.querySelector('.navbar').classList.add('default');
        document.querySelector('.navbar').classList.remove('top-nav-collapse');
      }
      ost = cOst;
    });
    // End [ Menu hide/show on scroll ]
    var wow = new WOW({
      animateClass: 'animated'
    });
    wow.init();
  </script>
  <!-- [Page Specific JS] end -->
</body>

</html>