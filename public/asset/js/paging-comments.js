$(document).ready(function () {
    // Affiche la pagination à la fin de la liste des commentaires
    $(".paging-container").slice(1, 0).show();
    $("#loadMore").on("click", function (
        e
    ) {
        e.preventDefault();
        $(".paging-container:hidden").slice(1, 0).slideDown();
        if ($(".comment:hidden").length === 0) {
            $(".paging-container").addClass("paging-block");
        }
    });
});