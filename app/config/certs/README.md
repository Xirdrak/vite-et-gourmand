# Certificat CA pour la base MySQL Aiven

La connexion a la base manageee Aiven se fait en SSL (obligatoire).
Doctrine a besoin du certificat CA d'Aiven pour verifier la connexion.

## Comment l'obtenir

1. Console Aiven -> service MySQL -> onglet "Overview"
2. Section "Connection information" -> "CA Certificate" -> bouton de telechargement
3. Enregistrer le fichier ici sous le nom exact : `aiven-ca.pem`

Le fichier `aiven-ca.pem` est reference dans `config/packages/doctrine.yaml`
(bloc `when@prod`) via `PDO::MYSQL_ATTR_SSL_CA`.

Un certificat CA est une information publique (pas un secret), il peut donc
etre versionne pour etre embarque dans l'image de deploiement.
