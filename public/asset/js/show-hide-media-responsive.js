$(document).ready(function () {
    // Affiche ou masque les médias
    $("#button-show-hidden-medias").click(function () {
        $(".medias").slideToggle("slow");
    });

    // Affiche le bouton "Afficher les médias"
    $("#showMedia").on("click", function (
        e
    ) {
        e.preventDefault();
        $("#showMedia").addClass("d-none");
        $("#hideMedia").removeClass("d-none");
    });

    // Affiche le bouton "Masquer les médias"
    $("#hideMedia").on("click", function (
        e
    ) {
        e.preventDefault();
        $("#showMedia").removeClass("d-none");
        $("#hideMedia").addClass("d-none");
    });
});

$(document).ready(function () {
    // Ajoute la nouvelle classe 'clicked' sur l'élément
    $("#showMedia").on("click",function () {
        $(".medias").addClass("clicked");
    });
});
