$(document).ready(function(){
    // Affiche les figures 5 par 5
    $(".trick").slice(0, 15).show();
    $("#loadMore").on("click", function(e){
        e.preventDefault();
        $(".trick:hidden").slice(0, 5).slideDown();
        if($(".trick:hidden").length === 0) {
            $("#loadMore").text("Ajouter de nouvelles figures").addClass("noContent");
        }
    });
});