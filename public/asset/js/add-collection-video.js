// Support de collection de vidéos
let $videoCollectionHolder;

// Configurer un lien "ajouter une video"
let $addVideoButton = $("<button type=\"button\" class=\"add_video_link\">Ajouter une url</button>");
let $newVideoLinkLi = $("<li></li>").append($addVideoButton);

// Ajoute un lien au bas de la liste des vidéos
jQuery(document).ready(function () {
    // Récupère l'ul qui contient la collection des vidéos
    $videoCollectionHolder = $("ul.videos");

    // Ajoute l'ancre "Ajouter une vidéo" et ajoute li aux vidéos ul
    $videoCollectionHolder.append($newVideoLinkLi);

    /*
    // Ajoute un lien de suppression à tous les éléments li du formulaire de balise existants
    $collectionHolder.find('li').each(function() {
        addVideoFormDeleteLink($(this));
    });
    */

    // Compte les entrées du formulaire actuelles que nous avons
    // indexé lors de l'insertion d'un nouvel élément
    $videoCollectionHolder.data("index", $videoCollectionHolder.find(":input").length);

    $addVideoButton.on("click", function () {
        // Ajoute un nouveau formulaire de vidéo
        addVideoForm($videoCollectionHolder, $newVideoLinkLi);
    });
});

// Ajoute un lien pour ajouter une vidéo
function addVideoForm(
    $collectionHolder,
    $newVideoLinkLi
) {
    // Obtient le prototype de données
    let prototype = $collectionHolder.data("prototype");

    // Récupère le nouvel index
    let index = $collectionHolder.data("index");

    let newForm = prototype;

    // Remplace '__name__' dans le code HTML du prototype par
    // un nombre basé sur le nombre d'éléments que nous avons
    newForm = newForm.replace(/__name__/g, index);

    // Augmente l'index de +1 pour l'élément suivant
    $collectionHolder.data("index", index + 1);

    // Afficher le formulaire dans un li, avant le lien li "Ajouter une vidéo"
    let $newFormLi = $("<li></li>").append(newForm);
    $newVideoLinkLi.before($newFormLi);

    // Ajoute un lien de suppression au nouveau formulaire
    addVideoFormDeleteLink($newFormLi);
}

// Ajoute un lien de suppression à la vidéo
function addVideoFormDeleteLink(
    $tagFormLi
) {
    let $removeFormButton = $("<button type=\"button\">Supprimer l\'url</button>");
    $tagFormLi.append($removeFormButton);

    $removeFormButton.on("click", function () {
        // Supprime le li du formulaire de la vidéo
        $tagFormLi.remove();
    });
}