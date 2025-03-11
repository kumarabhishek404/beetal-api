jQuery(document).ready(function ($) {
  if ($(".text-carousel").length) {
    $(".text-carousel").slick({
      dots: false,
      arrows: false,
      autoplay: true,
      autoplaySpeed: 3000,
      fade: true,
      cssEase: "ease-in",
    });
  }
});
