<?php

return [
    'page_title' => [
        'dashboard' => 'Tauler de control',
        'detail' => '%entity_as_string%',
        'edit' => 'Modificar %entity_label_singular%',
        'exception' => 'Error|Errors',
        'index' => '%entity_label_plural%',
        'new' => 'Nou %entity_label_singular%',
    ],

    'datagrid' => [
        'hidden_results' => 'Alguns resultats no es poden mostrar perquè no tens prou permisos',
        'no_results' => 'No s\'han trobat resultats.',
    ],

    'paginator' => [
        'counter' => '<strong>%start%</strong> - <strong>%end%</strong> de <strong>%results%</strong>',
        'first' => 'Primera',
        'last' => 'Última',
        'next' => 'Següent',
        'number_size' => 'Mostra',
        'previous' => 'Anterior',
        'results' => '{0} Cap resultat|{1} <strong>1</strong> resultat|]1,Inf] <strong>%count%</strong> resultats',
        'to' => 'de',
    ],

    'label' => [
        'empty' => 'Buit',
        'false' => 'No',
        'form.empty_value' => 'Ningú',
        'inaccessible' => 'Inaccessible',
        'inaccessible.explanation' => 'Aquest camp no té un mètode "getter" o la propietat associada no és pública',
        'null' => 'Null',
        'nullable_field' => 'Deixar buit',
        'object' => 'Objecte PHP',
        'true' => 'Sí',
    ],

    'field' => [
        'code_editor.view_code' => 'Veure codi',
        'text_editor.view_content' => 'Veure contingut',
    ],

    'global' => [
        'go_back' => 'Tornar',
        'no' => 'No',
        'yes' => 'Sí',
    ],

    'action' => [
        'add_new_item' => 'Afegir un element',
        'cancel' => 'Cancel·lar',
        'choose_file' => 'Tria un fitxer',
        'close' => 'Tancar',
        'create' => 'Guardar',
        'create_and_add_another' => 'Crear i afegir-ne un altre',
        'create_and_continue' => 'Crear i continuar editant',
        'delete' => 'Borrar',
        'deselect' => 'Desseleccionar',
        'detail' => 'Veure',
        'edit' => 'Modificar',
        'entity_actions' => 'Accions',
        'go_back' => 'Cancel·lar',
        'index' => 'Tornar al llistat',
        'new' => 'Crear %entity_label_singular%',
        'remove_item' => 'Eliminar aquest element',
        'save' => 'Guardar',
        'save_and_continue' => 'Desar i continuar editant',
        'search' => 'Buscar',
    ],

    'batch_action_modal' => [
        'action' => 'Continuar',
        'content' => 'Aquesta acció no es pot desfer.',
        'title' => 'S\'aplicarà l\'acció %action_name% a %num_items% element(s).',
    ],

    'delete_modal' => [
        'content' => 'Aquesta acció no es pot desfer.',
        'title' => 'Realment vols esborrar aquest element?',
    ],

    'filter' => [
        'button.apply' => 'Aplicar',
        'button.clear' => 'Netejar',
        'label.contains' => 'conté',
        'label.ends_with' => 'acaba amb',
        'label.exactly' => 'exactament',
        'label.is_after' => 'és després',
        'label.is_after_or_same' => 'és després o el mateix',
        'label.is_before' => 'és abans',
        'label.is_before_or_same' => 'és abans o el mateix',
        'label.is_between' => 'està entre',
        'label.is_equal_to' => 'és igual a',
        'label.is_greater_than' => 'és més gran que',
        'label.is_greater_than_or_equal_to' => 'és més gran o igual a',
        'label.is_less_than' => 'és menor que',
        'label.is_less_than_or_equal_to' => 'és menor o igual a',
        'label.is_not_equal_to' => 'no és igual a',
        'label.is_not_same' => 'no conté (and)',
        'label.is_same' => 'conté (or)',
        'label.not_contains' => 'no conté',
        'label.not_exactly' => 'no exactament',
        'label.starts_with' => 'comença amb',
        'title' => 'Filtres',
    ],

    'form' => [
        'are_you_sure' => 'No has desat els canvis fets en aquest formulari.',
        'slug.confirm_text' => 'Si canvies l\'slug, pots trencar els enllaços d\'altres pàgines.',
        'tab.error_badge_title' => 'Una entrada no vàlida|%count% entrades no vàlides',
    ],

    'user' => [
        'anonymous' => 'Usuari anònim',
        'exit_impersonation' => 'Sortir de la suplantació',
        'logged_in_as' => 'Connectat com a',
        'logout' => 'Desconectar',
        'sign_out' => 'Tanca la sessió',
        'unnamed' => 'Usuari sense nom',
    ],

    'login_page' => [
        'forgot_password' => 'Has oblidat la teva contrasenya?',
        'password' => 'Contrasenya',
        'remember_me' => 'Recorda\'m',
        'sign_in' => 'Iniciar sessió',
        'username' => 'Nom d\'usuari',
    ],

    'exception' => [
        'entity_not_found' => 'Aquest element ja no està disponible.',
        'entity_remove' => 'Aquest element no es pot suprimir perquè altres elements en depenen.',
        'forbidden_action' => 'L\'acció sol·licitada no es pot dur a terme en aquest element.',
        'insufficient_entity_permission' => 'No tens permís per accedir a aquest element.',
    ],

    'autocomplete' => [
        'loading-more-results' => 'Carregant més resultats…',
        'no-more-results' => 'No hi ha més resultats',
        'no-results-found' => 'No s\'han trobat resultats',
    ],

    'detail' => [
        'empty_observation' => 'No hi ha observacions',
        'last_updated_at' => 'Darrera modificació:',
    ],

    'genres' => [
        'modal' => [
            'edit_title' => 'Editar gènere',
            'new_title' => 'Afegir gènere',
        ],
    ],

    'lots' => [
        'detail' => [
            'cdl' => 'CDL',
            'copies_title' => 'Exemplars del lot',
            'library' => 'Biblioteca',
            'lot_disable' => 'Aquest lot es troba en estat de baixa.',
            'lot_disposed' => 'Aquest lot està disponible i es pot demanar.',
            'lot_reserved' => 'Aquest lot està marcat com a reservat.',
            'municipality' => 'Municipi',
            'num_lang' => 'Num. Exemplars',
            'recived_aprox_at' => 'Data estimada recepció',
            'recived_at' => 'Data recepció',
            'requested_at' => 'Data Petició',
            'returned_aprox_at' => 'Data estimada retorn',
            'returned_at' => 'Data retorn',
            'returned_force_at' => 'Data Pt. Retorn',
            'state' => 'Estat',
            'status_available' => 'Disponible',
            'status_requested' => 'Demanat',
            'status_prepared' => 'En procés',
            'status_in_transit' => 'En trànsit',
            'status_in_library' => 'Biblioteca',
            'status_is_return' => 'Pt. Retorn',
            'status_is_collected' => 'Pt. Recollida',
            'status_is_returned' => 'En retorn',
        ],

        'forms' => [
            'add_copie' => '+ Afegir exemplar',
            'num_copies' => 'NUM.EXEMPLARS',
        ],
    ],

    'shipments' => [
        'lots' => 'Recepció de lots',
        'max_entry_date' => 'Data màxima d\'entradada peticions',
        'route_select' => 'RUTA',
        'shipments' => 'Trameses',
    ],

    'clubs' => [
        'detail' => [
            'authorship' => 'Autoria',
            'cat' => 'CAT',
            'es' => 'ES',
            'historic_lots' => 'Històric de lots',
            'num_picked_lot' => 'Núm tramesa de lot',
            'others' => 'Altres',
            'petition' => 'Petició',
            'returnd' => 'Retorn',
            'title' => 'Títol',
        ],
    ],

    'libraries' => [
        'detail' => [
            'Actions' => 'Accions',
            'arrived' => 'Arribada',
            'authorship' => 'Autoria',
            'cdl' => 'CDL',
            'estimate_arived' => 'Est.Arrib',
            'num_ship' => 'NºTramesa',
            'petition' => 'Petició',
            'returnd_limit' => 'Ret.Limit',
            'state' => 'Estat',
            'title' => 'Títol',
            'use_lots' => 'Lots en ús',
        ],
    ],

    'my_clubs' => [
        'authorship' => 'Autoria',
        'code' => 'CODI',
        'description' => 'DESCRIPCIÓ',
        'edit' => 'Editar',
        'entry_year' => 'ANY D\'ENTRADA',
        'extern' => 'EXTERN',
        'genre' => 'GÈNERE',
        'library' => 'BIBLIOTECA',
        'name_library_club' => 'NOM CLUB DE LECTURA',
        'num_exem' => 'NUM DE EXEMPLARS',
        'observations' => 'OBSERVACIONS',
        'owner' => 'PROPIETAT',
        'status' => 'ACTIU',
        'status_table' => 'DISPONIBILITAT',
        'tipology' => 'TIPOLOGIA',
        'title' => 'Títol',
        'year_table' => 'ANY',
    ],

    'modal' => [
        'title' => 'TÍTOL',
        'title_config' => 'Configuració de paràmetres',
        'genre' => 'GÈNERE',
        'active' => 'Actiu',
        'author' => 'AUTORIA',
        'sign' => 'SIGNATURA',
        'description' => 'D\'ESCRIPCIÓ FÍSICA',
        'num' => 'NUM. EXEMPLARS',
        'library' => 'BIBLIOTECA',
        'cdl' => 'CLUB DE LECTURA',
        'text' => 'Text comentaris',
        'text_info' => 'Text de descripciò.',
        'date' => 'DATA APROXIMADA D\'ENTREGA',
        'num_lot' => 'Nº TRAMESA DE LOT',
        'save' => 'Guardar',
        'close' => 'Tancar',
        'date_max' => 'Dies previs per l\'enviament del lot',
        'max_library' => 'Dies màxims per retorn a biblioteca',
        'max_bus' => 'Dies màxims per retorn a bibliobus  ',
        'max_library_lf' => 'Dies màxims per retorn a biblioteca (Lots LF)',
    ],

    'index' => [
        'route' => [
            'historic' => 'Históric',
            'lots' => 'Lots',
            'clubs' => 'Clubs',
            'clubslots' => 'Clubs/Lots',
            'genre' => 'Géneres',
            'municipality' => 'Municipis',
            'zone' => 'Comarques',
        ],
    ],

    'header' => [
        'manual_title_link' => 'Ajuda',
    ],

];
