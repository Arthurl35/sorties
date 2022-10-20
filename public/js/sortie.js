function hideFormLieu(){
    $('.f_lieu').removeAttr('required');
    $('div:has(> .f_lieu)').hide();
    $('.btn_add_lieu').show();
}

function showFormLieu(){
    $('div:has(> .f_lieu)').show();
    $('.f_lieu').attr('required', 'required');
    $('.btn_add_lieu').hide();
}

function setNewListLieu(){
    if($('#sortie_lieu option[value="0"]').length === 0){
        $('#sortie_lieu').append($('<option value="0" selected>new</option>'));
    }
}

function removeNewListLieu(){
    $('#sortie_lieu option[value="0"]').remove();
}

function sortie(){

    removeNewListLieu();
    hideFormLieu();

    $('#sortie_lieu').change(function() {
        hideFormLieu();
        removeNewListLieu();
    });

    $('.btn_add_lieu').click(function() {
        showFormLieu();
        setNewListLieu();
    });
}