# CMS_project

 Ce projet met en œuvre les étapes clés de l'installation à la personnalisation pour créer une plateforme centrée sur le thème **"meubles et accessoires"**, avec le thème WordPress **Ciri - Furniture & Interior WooCommerce Theme**.

## Fonctionnalités principales
- **Mobile-first, Responsive Layout**
- **Custom Colors**
- **Custom Header**
- **Social Links**
- **Mega Menu**
- **Post Formats**

## Plugins utilisés
- **Elementor Page Builder** : Un constructeur de pages visuel basé sur des blocs.
- **LA-Studio Core** : Ajoute des fonctionnalités spécifiques au thème, comme des widgets personnalisés.
- **LA-Studio Header Builder** : Permet de personnaliser l'en-tête du site.
- **LA-Studio Elements for Elementor** : Ajoute des widgets spécifiques pour Elementor.
- **Contact Form 7** : Crée des formulaires de contact simples et flexibles.
- **Slider Revolution** : Crée des sliders animés et interactifs.
- **WooCommerce** : Transforme WordPress en boutique en ligne.

---

## Prérequis techniques
1. **Installer un serveur local** :
   - Téléchargez et installez [XAMPP](https://www.apachefriends.org/download.html).
2. **Télécharger WordPress** :
   - Assurez-vous d'avoir tous les fichiers nécessaires de WordPress, y compris :
     - `wp-content`
     - `wp-config.php`
     - Autres fichiers essentiels.

---

## Installation et configuration

### 1. Installer et configurer XAMPP
- Démarrez les services **Apache** et **MySQL** via XAMPP.

### 2. Placer le projet dans le répertoire htdocs
- Copiez le contenu du dossier `wordpress` et placez-le dans le répertoire `htdocs` de XAMPP.

### 3. Créer une base de données
1. Accédez à **phpMyAdmin** via `http://localhost/phpmyadmin`.
2. Cliquez sur l'onglet **Bases de données**.
3. Dans le champ **Créer une base de données**, saisissez un nom (ex. : `wordpress_db`).
4. Sélectionnez **utf8_general_ci** comme interclassement.
5. Cliquez sur **Créer**.

### 4. Importer la base de données
1. Après avoir créé la base de données, cliquez sur son nom dans la liste à gauche.
2. Cliquez sur l'onglet **Importer**.
3. Sélectionnez le fichier `.sql` exporté.
4. Cliquez sur **Exécuter** pour importer la base de données.

### 5. Configurer WordPress
1. Ouvrez le fichier `wp-config.php` dans un éditeur de texte.
2. Modifiez les lignes suivantes avec vos informations :

```php
/** Le nom de la base de données de WordPress */
define('DB_NAME', 'wordpress_db');

/** Votre identifiant MySQL */
define('DB_USER', 'root');

/** Le mot de passe de votre identifiant MySQL */
define('DB_PASSWORD', '');

/** Adresse de l’hôte MySQL */
define('DB_HOST', 'localhost');
```
3. Sauvegardez le fichier.

### 6. Lancer l'installation
1. Accédez à `http://localhost/<nom_du_projet>` (remplacez `<nom_du_projet>` par le nom du dossier où vous avez placé les fichiers WordPress).
2. Sélectionnez votre langue et cliquez sur **Continuer**.
3. Suivez les étapes pour configurer votre site :
   - **Titre du site**
   - **Identifiant admin**
   - **Mot de passe admin**
   - **Adresse e-mail**
4. Cliquez sur **Installer WordPress**.
   
### 7. Activer les plugins
1. Connectez-vous au tableau de bord WordPress.
2. Allez dans **Extensions** > **Extensions installées**.
3. Activez les plugins nécessaires pour votre thème.

---

- Se connecter au tableau de bord :
  * Cliquez sur le bouton Se connecter.
  * Utilisez l'identifiant et le mot de passe définis pour accéder à l'administration WordPress.

## Identifiant

### Admin
- **Nom d'utilisateur** : admin
- **Mot de passe** : admin123

### Contributeur
- **Nom d'utilisateur** : user1
- **Mot de passe** : user123

### Éditeur
- **Nom d'utilisateur** : user2
- **Mot de passe** : usere123

