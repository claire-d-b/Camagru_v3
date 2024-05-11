Le but est de créer un site web en se rapprochant du MVC : model view controller.
Dans l’idée, c’est de différencier le MODEL : la structure de la base de données contre la
VIEW : l’interface utilisateur et le CONTROLLER : ce qui fait le lien entre la bdd et l’interface.
Dans mon projet, chaque page contient une view composée de balises HTML, et un controller qui permet de solliciter la bdd pour mettre à jour les informations affichées. Le ’Model’ de mon site serait plutôt dans le fichier d’initialisation de la bdd postrgesql : soit initdb.sql.

Le site est une banque d’images superposées, postées par des utilisateurs, commentées et likées par quiconque s’est créé un profil.

Mon choix a été de respecter l’idée de ne pas avoir de framework en front ou en back. J’ai travaillé en vanilla PHP pour tout ce qui est ‘controller’ : gestion de formulaire et récupération des données à injecter dans le back. Je pense que peu de sites utilisent le vanilla PHP mais plutôt des frameworks tels que Laravel ou Symphony. Mon choix de coder en vanilla, c’est pour mieux comprendre les mécanismes de sollicitation de la base de données (fetch pour récupérer, insert / update / delete en tant que requêtes // bdd.)

Il n’y a pas non plus de framework «front» pour faciliter le design des pages et leur côté responsive.
Seul l’outil « flexbox » est utilisé en CSS, et le css est directement  déterminé dans les balises html via l’élément style. Ceci permet de se familiariser avec toutes les directives CSS existantes (ex: padding, background…)

Le projet est portable : trois containers communiquent entre eux :

- Celui de l’interface web avec le contenu des pages et nginx qui tourne en background

- Celui du php qui permet d’interpréter les directives PHP. Php-fpm est une Serveur Application Interface (SAPI) qui permet la communication entre un serveur web et php, basée sur le protocole fastcgi.
Fastcgi est une technique qui permet la communication entre un serveur web et un logiciel indépendant, c’est une évolution de Common Gateway Interface (CGI.)
Postgresql est activé dans ce conteneur en tant qu’extension mentionnée dans le fichier d’initialisation de php: php.ini. Pdo-pgsql est aussi activé et implémente l’interface ‘Php Data Object’ pour permettre l’accès par php à la bdd Postgres.

- Celui de la base de données.

Pour lancer le projet, assurez vous que le client docker tourne.
Créez un fichier .env à la racine et spécifiez les constantes relatives à la bdd: 
- PGPASSFILE=
- PGHOST=
- PGUSER=
- PGDATABASE=
- PGPASSWORD=
- PGPORT=
- CAMAGRU_MAIL=

Pour lancer le projet, tapez:

- sh start.sh

Pour vérifier si les containers tournent:
- docker ps

Pour entrer dans un container tel que celui de la base de données et y faire des actions:
- docker exec -it <container id> sh

Pour supprimer tous les volumes (les pages .php pour permettre leur modification):
- docker-compose down -v

Pour stopper et supprimer les containers qui tournent :
- docker stop $(docker ps -aq)
- docker rm $(docker ps -aq)

Pour supprimer les images docker :
- docker rmi $(docker images -q)

Si vous n'avez pas d'idées de tests, il y en a dans le fichier tests.txt
