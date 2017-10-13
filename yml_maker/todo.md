# Listes des choses à finir sur le module yml_maker

## Settings

#### Paramètre pour lister les paths ou le client à le droit de créer un nouveau fichier
Une table avec au moins deux champs. Un champ varchar description du path, et un autre pour le path lui meme.

Bonus: Mettre dautres columns pour les roles.


#### Paramètre pour vérifier le nom du fichier. Empecher des noms comme routings.
Un champ texte, ou on liste les noms en les séparant d'une virgule.

#### Paramétre 


## Builder form

### Changement de projet ! Plus de drag and drop. mais un champ avec un integer pour l'indent.

#### Récupérer la liste des paths disponibles pour la création d'un nouveau fichier.

#### Créer du coup un if. Si c'est un nouveau fichier, on offre la liste. Si c'est un edit, on met le champ en caché.
#### Idem un if sur le nom du fichier si jamais c'est une création ou un edit.
#### If si le fichier est nouveau ou pas pour remplir la table ou pas. Sans doute deux fonctions différentes.


### Terminer la validation, et l'écriture du fichier.
Arriver à récupérer les données du pid, etc.

### Terminer l'ajax de l'ajout et suppression de lignes.

### Terminer l'ajax de manipulation des rows. == PLUS D'ACTUALITE. DU BONUS. 



## Templates (bonus)
* Créer le systeme d'injection de templates.
* La page pour créer les templates.
* Changer la form pour permettre l'ajout des templates. Une liste (select), avec un champ ou on indiq le numéro de ligne ou on ajoute le template.
