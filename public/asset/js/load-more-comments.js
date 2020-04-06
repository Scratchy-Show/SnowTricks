$(document).ready(function(){
    // Affiche les commentaires 4 par 4
    $(".comment").slice(0, 4).show();
    $("#loadMore").on("click", function(e){
        e.preventDefault();
        $(".comment:hidden").slice(0, 4).slideDown();
        if($(".comment:hidden").length == 0) {
            $("#loadMore").text("Tous est affiché").addClass("noContent");
        }
    });

    // Affiche la pagination à la fin de la liste des commentaires
    $(".paging-container").slice(1, 0).show();
    $("#loadMore").on("click", function(e){
        e.preventDefault();
        $(".paging-container:hidden").slice(1, 0).slideDown();
        if($(".comment:hidden").length == 0) {
            $(".paging-container").addClass("paging-block");
        }
    });
})