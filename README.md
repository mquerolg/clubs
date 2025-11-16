# Clubs de lectura
Aquest projecte està construït amb Symfony 5.4 i PHP 7.4 i utilitza la llibreria oci per fer la connexió a una base de dades ORACLE.

---

## Eines
- PHP 7.4 (Amb oci para ORACLE)
- Symfony 5.4
- Composer 2.0
- EasyAdmin 3.0
- Node 19.1.9
- Npm 8.18.3
- Visual Studio Code

---

## Recomanacions per a Visual Studio Code
Per millorar l'experiència de desenvolupament al Visual Studio Code, es recomana instal·lar els plugins següents:
- PHP IntelliSense
- Symfony 5 Snippets
- Twig Language 2
- PHP CS Fixer (configuració recomanada a .php_cs)
- PHP DocBlocker

Aquests plugins augmentaran la velocitat i eficiència en l'escriptura de codi i milloraran la integració amb els frameworks i llibreries utilitzades al projecte.

---

## Configurar en local
Perquè el projecte funcioni en local s'ha de configurar el fitxer .env amb les variables:

```
### Ruta a la DB ###
DB_HOST=
### Nom de la DB ###
DB_NAME=
### Nom d'usuari DB ###
DB_USER=
### Contrasenya d'usuari DB ###
DB_PASSWORD=
### ID del perfil d'administrador ###
ADMIN_PROFILE_ID=
### Són variables de test i han d'estar en false ###
IS_USER_ADMIN=false
IS_USER_LOGIN=false
```

Addicionalment s'ha de crear la carpeta uploads al directori arrel de l'aplicació o generar-hi un enllaç simbólic per poder pujar fitxers des del LotCrudController.

Per llançar el projecte caldrà:
```
npm install
npm run dev

php composer.phar install
php composer.phar dump-env dev

php -S localhost:8000 -t ./public
```

---

## Assets
Al directori `assets` trobarem els fulls d'estil i llibreries js principals. La tecnologia utilitzada per fer els fulls d'estil és mitjançant scss. El conjunt de fitxers scss i js, es compilen i s'incorporen al directori públic mitjançant:

Desenvolupament:
```
npm run dev
```
Producció:
```
npm run prod
```

La configuració d'exportació la podem trobar al fitxer webpack.config.js

---

## Templates
El directori `templates` conté les plantilles twig utilitzades al projecte.
A més, s'inclou el fitxer templates/Registry/TemplateRegistry.php. Aquest fitxer conté una redeclaració de la classe TemplateRegistry del mateix easyadmin on s'emmagatzema un llistat de totes les plantilles com a: 

```
‘nom’ => ‘ruta a plantilla’
```

---

## Translations
El directori translations conté els fitxers
- `EasyAdminBundle.ca.php` : Encarregat d'acollir les traduccions que són anomenades des de les vistes (twig).
- `messages.ca.yaml` : Encarregat d'acollir les traduccions que són generades des del CrudControler i que arriben en forma de variable ja traduïda a la vista.

L'extensió `.ca` fa referència a l'idioma que emmagatzema.

---

## SRC
A continuació es detallen els directoris d'interès dins del directori `src`
### Command
El directori command conté les ordres que són executades mitjançant la terminal de symfony.
````
php bin/console
````
En particular trobarem els fitxers:
- `GenerateReportCommand.php` : Encarregat de l'actualització de les taules d'informes.
- `NotificationsCommand.php` : Encarregat de generar l'enviament de correus a SAP i recordatoris de lots amb data de tornada excedida.

### Diba
El directori `diba` conté classes que connecten amb les api de diba per obtenir biblioteques i bibliogràfics. Així com la case que permet l´ús de `SAMC`. Aquests són els fitxers
- `DibaApi.php\Biblioteca.php\Book.php\Exception.php` : Per a la connexió amb api's.
- `SamcService.php` : Per a la connexió amb `SAMC`.

També trobarem els directoris següents:

- `admin` : Conté les classes esteses o sobreescrites d'easyadmin en particular les que s'apliquen a filtres.

- `doctrine` : Conté les classes de Doctrine (Symfony) customitzades per al treball a ORACLE amb les especificacions actuals en què corre per al cas de CLUBS.

- `helpers` : Conté classes de suport (aquestes classes tenen la particularitat de prendre només mètodes estàtics).

### Controller
Al directori `controlers` trobarem:
- `DashboardController.php` : Fitxer de configuració principal d'easyadmin, emmagatzema tant la configuració global com els elements que conformen l'index o menú esquerre de l'aplicació
- `RouteController.php` : És una extensió de DashboardController on s'emmagatzemen totes les rutes que es fan servir a l'aplicatiu.
- `SecurityController.php` : Conté la lògica per a la validació d'un usuari (login).
- `admin`: Directori que conté els crud controller associats a les vistes de l'aplicació, aquests es detallen més endavant.


## Entity
El directori `entity` conté les classes que simbolitzen l'estructura de cada taula:

- `.\` : A l'arrel del directori es troben les classes principals que contenen el model complet de cada taula a la DB.
- `Cruds` : Conté entitats que són utilitzades per generar un Crud salvant la limitació d'easyadmin que no permet declarar més d'un CrudController per entitat.
- `Deleted` : Són entitats que ignoren el deletesoft.
- `Reports` : Conté les entitats que s'utilitzen en el cas dels informes.
- `Support` : Conté entitats de suport que permeten carregar models més lleugers que els que trobarem al directori arrel utilitzats en casos en què no es requereix tot el relacional.
- `Traits` : Conté `traits` que poden ser utilitzats en més d'una entitat per no redeclarar codi.

---


## Cruds
A la carpeta `src/controller/admin` trobarem els CrudController. Els CrudController són controladors que s'estenen des d'un controlador principal AdminCrudController, on podem trobar la definició de totes les funcions que intervenen en la construcció de qualsevol CrudController. A nivell d'organització els CrudController que s'utilitzen en informes s'han inclòs a la carpeta Reports que trobarem al mateix directori.

Cada CrudControler controla 4 possibles vistes, aquestes són:
- `Index` : Representació de la taula
- `View` : Vista detallada d'un objecte de la taula
- `Edit` : Visualització d'edició d'un objecte de la taula
- `New` : Vista de creació d'un nou objecte.

A continuació es mostra un exemple de l'estructura bàsica d'un CrudController, les seccions i la configuració:

```
/**
 * ExampleCrudController
 */
class ExampleCrudController extends AdminCrudController
{
    /**
     * Aquesta funció és obligada i serveix per declarar l'entitat
     * associada a aquest CrudControler, aquesta entitat ha de ser única
     * per a cada CudController.
     */
    public static function getEntityFqcn(): string
    {
        return Clubs::class;
    }

    /**
     * A partir del constructor podem definir les diferents
     * opcions del CrudController
     */
    public function __construct()
    {
        // Permet establir laccés per part de ladministrador i de l'usuari
        // a les vistes del CrudController.
        // True per accedir-hi False per negar l'accés.
        $this->setAdminAccess(true);
        $this->setUserAccess(true);

        // Podem establir plantilles específiques per a cada vista del 
        // CrudController. Les vistes han d'estar declarades al 
        // TemplateRegistry
        $this->setTemplateDetailName('crud/details/example');
        $this->setTemplateNewName('crud/new/example');
        $this->setTemplateIndexName('crud/index/example');
        $this->setTemplateEditName('crud/edit/example');

        // Permet donar un nom no genèric al fitxer d'exportació
        // de la taula. El nom prendrà la forma example_fecha.csv
        $this->setFileNameExportData('example');

        // Permet donar un ordre per defecte a la taula
        $this->setDefaultSort(['entity.title' => 'ASC']);

        // Si és true s'apliquen funcions que poden ser definides per
        // modificar l'ordre a la taula, podeu consultar més sobre 
        // aquestes funcions a l'AdminCrudController
        $this->setSortable(true);

        // Permet especificar un nom específic no genèric
        // per al títol de cada vista, els noms s'han de
        // declarar a les traduccions. Si no s'especifiquen, 
        // s'utilitza el nom de l'entitat com a label.
        $this->setTitels([
            'index' => 'example_index',
            'detail' => 'example_detail',
            'edit' => 'example_edit',
            'new' => 'example_new',
        ]);
    }

    /**
     * La funció següent permet definir quins camps seran visibles
     * a la taula de l'index per a l'administrador.
     * Per a les diferents vistes es realitza de manera analoga amb:
     * configureAdminDetailFields, configureAdminNewFields, configureAdminEditFields
     * 
     * En el cas d'usuaris les funcions són iguals canviant Admin per User
     */
    public function configureAdminIndexFields(): iterable
    {
        return [
            // Cada camp es declara com:
            // ClasseTipus::new('camp de l'entitat', 'label del camp')
            // Les ClasseTipo es poden consultar a la documentació d'easyadmin.
            // Si no s'especifica label, es pren el nom del camp com a label.
            TextField::new('field', 'example_label'),
        ];
    }

    /**
     * La funció següent permet definir els filtres que apareixeran
     * a la vista index.
     */
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            // Cada camp es declara com:
            // ClasseTipus::new('camp de l'entitat', 'label del camp')
            // Podeu consultar els diferents filtres al directori Diba/Admin
            // ia la documentació d'easyadmin
            // Si no s'especifica label, es pren el nom del camp com a label.
            ->add(TextFilter::new('field', 'example_label'))
        ;
    }

    /**
     * La funció següent permet delarar els diferents botons i accions 
     * que trobarem a cada vista. Podem trobar més informació i exemples
     * a la documentació d'easyadmin
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions;
    }
}

```