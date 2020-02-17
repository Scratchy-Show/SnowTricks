// Support de collection d'images
let $pictureCollectionHolder;

// Configurer un lien "ajouter une image"
let $addPictureButton = $('<button type="button" class="add_picture_link">Ajouter une image</button>');
let $newPictureLinkLi = $('<li></li>').append($addPictureButton);

// Ajoute un lien au bas de la liste des images
jQuery(document).ready(function() {
    // Récupère l'ul qui contient la collection d'images
    $pictureCollectionHolder = $('ul.pictures');

    // Ajoute l'ancre "Ajouter une image" et ajoute li aux images ul
    $pictureCollectionHolder.append($newPictureLinkLi);

    /*
    // Ajoute un lien de suppression à tous les éléments li du formulaire de balise existants
    $collectionHolder.find('li').each(function() {
        addPictureFormDeleteLink($(this));
    });
    */

    // Compte les entrées du formulaire actuelles que nous avons
    // indexé lors de l'insertion d'un nouvel élément
    $pictureCollectionHolder.data('index', $pictureCollectionHolder.find(':input').length);

    $addPictureButton.on('click', function() {
        // Ajoute un nouveau formulaire d'images
        addPictureForm($pictureCollectionHolder, $newPictureLinkLi);
    });
});

// Ajoute un lien pour ajouter une image
function addPictureForm($collectionHolder, $newPictureLinkLi) {
    // Obtient le prototype de données
    let prototype = $collectionHolder.data('prototype');

    // Récupère le nouvel index
    let index = $collectionHolder.data('index');

    let newForm = prototype;

    // Remplace '__name__' dans le code HTML du prototype par
    // un nombre basé sur le nombre d'éléments que nous avons
    newForm = newForm.replace(/__name__/g, index);

    // Augmente l'index de +1 pour l'élément suivant
    $collectionHolder.data('index', index + 1);

    // Afficher le formulaire dans un li, avant le lien li "Ajouter une photo"
    let $newFormLi = $('<li></li>').append(newForm);
    $newPictureLinkLi.before($newFormLi);

    // Ajoute un lien de suppression au nouveau formulaire
    addPictureFormDeleteLink($newFormLi);
}

// Ajoute un lien de suppression à l'image
function addPictureFormDeleteLink($tagFormLi) {
    let $removeFormButton = $('<button type="button">Supprimer l\'image</button>');
    $tagFormLi.append($removeFormButton);

    $removeFormButton.on('click', function() {
        // Supprime le li du formulaire d'image
        $tagFormLi.remove();
    });
}