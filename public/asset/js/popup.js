/* Modal pour la galerie d'image d'une figure */
$(document).ready(function () {
    const modal = $("#pictureModal");
    const span = $(".close");
    const modalImg = $("#img");
    const captionText = $("#caption");

    const img = $(".imgModal");

    img.click(function () {
        modal.css("display", "block");
        modalImg.attr("src", this.src);
        captionText.html(this.alt);
    });

    span.click(function () {
        modal.css("display", "none")
    });
});