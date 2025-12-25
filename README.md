# 🎓 Système de Gestion de Dossiers Scolaires

Application web de gestion scolaire développée avec Symfony 7 et MySQL.

## 📋 Fonctionnalités

-   ✅ Gestion des élèves, classes et matières
-   ✅ Gestion des notes et calcul automatique des bulletins
-   ✅ Consultation des résultats par les parents/élèves
-   ✅ 4 rôles : Administrateur, Enseignant, Parent, Élève

## 🛠️ Technologies

-   **Backend**: Symfony 7.2
-   **Base de données**: MySQL 8.0 / MariaDB 10.4+
-   **ORM**: Doctrine
-   **PHP**: 8.2+

---

## ⚙️ Prérequis

Avant de commencer, assure-toi d'avoir installé:

-   ✅ **PHP 8.2+** (avec extensions `pdo_mysql` et `mysqli` activées)
-   ✅ **Composer** (gestionnaire de dépendances PHP)
-   ✅ **MySQL 8.0+** ou **MariaDB 10.4+**
-   ✅ **Git**
-   ✅ **Symfony CLI** (optionnel, mais recommandé)

### Vérifier les versions

```bash
php -v          # Doit afficher PHP 8.2.x ou supérieur
composer -V     # Doit afficher Composer 2.x
git --version   # Doit afficher git 2.x
mysql --version # Doit afficher MySQL/MariaDB
```

---

## 🚀 Installation

### 1. Cloner le Repository

```bash
git clone https://github.com/USERNAME/gestion_dossier_scolaire.git
cd gestion_dossier_scolaire
```

### 2. Installer les Dépendances PHP

```bash
composer install
```

⏳ Cette commande peut prendre 2-3 minutes.

### 3. Configurer la Base de Données

#### 3.1 Créer le fichier de configuration

```bash
# Windows PowerShell
copy .env.example .env.local

# Linux/Mac
cp .env.example .env.local
```

⚠️ **Important**: Le fichier `.env.local` **ne doit JAMAIS être commité** sur Git (il contient tes identifiants).

#### 3.2 Éditer `.env.local`

Ouvre le fichier `.env.local` et modifie la ligne `DATABASE_URL`:

```env
# Exemple avec XAMPP (Windows)
DATABASE_URL="mysql://root:@127.0.0.1:3306/school_records_management?serverVersion=mariadb-10.4.32&charset=utf8mb4"

# Exemple avec mot de passe
DATABASE_URL="mysql://root:ton_mot_de_passe@127.0.0.1:3306/school_records_management?serverVersion=mariadb-10.4.32&charset=utf8mb4"

# Exemple avec MySQL 8.0
DATABASE_URL="mysql://root:@127.0.0.1:3306/school_records_management?serverVersion=8.0.32&charset=utf8mb4"
```

**Paramètres à adapter:**

-   `root` → ton nom d'utilisateur MySQL
-   `:@` → ton mot de passe (vide = `@`, sinon `:password@`)
-   `school_records_management` → nom de la base de données
-   `mariadb-10.4.32` → ta version de MySQL/MariaDB (vérifie avec `mysql --version`)

### 4. Créer la Base de Données

```bash
# Créer la base de données
php bin/console doctrine:database:create

# Créer les tables
php bin/console doctrine:migrations:migrate
```

Réponds **"yes"** quand on te demande confirmation.

### 5. Charger les Données de Test (Fixtures)

```bash
php bin/console doctrine:fixtures:load
```

Réponds **"yes"** pour confirmer.

📊 **Données générées:**

-   1 administrateur
-   ~12 enseignants
-   30 parents
-   ~300 élèves
-   12 classes (6ème à 3ème, sections A/B/C)
-   8 matières
-   ~4500 notes

### 6. Démarrer le Serveur

#### Option A: Avec Symfony CLI (Recommandé)

```bash
symfony server:start
```

Puis ouvre: **http://127.0.0.1:8000**

#### Option B: Avec le serveur PHP intégré

```bash
php -S localhost:8000 -t public
```

Puis ouvre: **http://localhost:8000**

#### Option C: Avec XAMPP/Apache

Place le projet dans `C:\xampp\htdocs\` et ouvre: **http://localhost/gestion_dossier_scolaire/public**

---

## 🔐 Comptes de Test

Après avoir chargé les fixtures, tu peux te connecter avec:

| Rôle           | Email            | Mot de passe  |
| -------------- | ---------------- | ------------- |
| Administrateur | admin@school.com | admin123      |
| Enseignant     | (email généré)   | enseignant123 |
| Parent         | (email généré)   | parent123     |
| Élève          | (email généré)   | eleve123      |

💡 **Pour voir tous les emails générés:**

```bash
# Windows (avec XAMPP)
C:\xampp\mysql\bin\mysql.exe -u root

# Linux/Mac
mysql -u root -p

# Puis dans MySQL:
USE school_records_management;
SELECT email, role FROM utilisateur LIMIT 20;
```

---

## 📁 Structure du Projet

```
gestion_dossier_scolaire/
├── config/              # Configuration Symfony
├── migrations/          # Migrations de base de données
├── public/              # Point d'entrée web (index.php)
├── src/
│   ├── Controller/      # Contrôleurs
│   ├── Entity/          # Entités Doctrine (modèles)
│   ├── Repository/      # Repositories
│   ├── Form/            # Formulaires
│   └── DataFixtures/    # Données de test
├── templates/           # Templates Twig
├── var/                 # Cache et logs
├── .env.example         # Template de configuration
├── .env.local          # Configuration locale (NE PAS COMMIT)
└── composer.json       # Dépendances PHP
```

---

## 🔄 Workflow Git (Collaboration)

### Avant de Commencer à Travailler

```bash
# Récupérer les dernières modifications
git pull origin main

# Installer/mettre à jour les dépendances
composer install

# Vider le cache
php bin/console cache:clear
```

### Après avoir Fait des Modifications

```bash
# Voir les fichiers modifiés
git status

# Ajouter les fichiers au commit
git add .

# Créer un commit avec un message descriptif
git commit -m "feat: Ajout de la fonctionnalité X"

# Envoyer vers GitHub
git push origin main
```

### ⚠️ Fichiers à NE JAMAIS Commit

Ces fichiers sont déjà dans `.gitignore`:

-   ❌ `.env.local` (configuration locale avec identifiants)
-   ❌ `var/cache/` (cache)
-   ❌ `var/log/` (logs)
-   ❌ `vendor/` (dépendances Composer)

---

## 📚 Commandes Utiles

### Doctrine (Base de Données)

```bash
# Créer une nouvelle entité
php bin/console make:entity NomEntite

# Créer une migration après modification d'entité
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Valider le schéma
php bin/console doctrine:schema:validate

# Recharger les fixtures
php bin/console doctrine:fixtures:load
```

### Symfony Maker

```bash
# Créer un contrôleur
php bin/console make:controller NomController

# Créer un formulaire
php bin/console make:form NomFormType

# Créer un CRUD complet
php bin/console make:crud NomEntite

# Créer l'authentification
php bin/console make:auth
```

### Autres Commandes

```bash
# Vider le cache
php bin/console cache:clear

# Voir toutes les routes
php bin/console debug:router

# Voir les services disponibles
php bin/console debug:container
```

---

## 🐛 Résolution de Problèmes

### Problème 1: "could not find driver"

**Cause:** Les extensions PDO MySQL ne sont pas activées.

**Solution:**

1. Ouvre `C:\xampp\php\php.ini` (Windows) ou `/etc/php/8.2/cli/php.ini` (Linux)
2. Trouve et décommente (enlève le `;`):

```ini
   extension=pdo_mysql
   extension=mysqli
```

3. **Windows:** Redémarre Apache dans XAMPP
4. **Linux:** Redémarre PHP-FPM: `sudo systemctl restart php8.2-fpm`

### Problème 2: "Access denied for user"

**Cause:** Identifiants MySQL incorrects dans `.env.local`

**Solution:** Vérifie ton `DATABASE_URL` dans `.env.local`

### Problème 3: "No such file or directory" pour .env.local

**Cause:** Tu n'as pas créé le fichier `.env.local`

**Solution:**

```bash
copy .env.example .env.local  # Windows
cp .env.example .env.local    # Linux/Mac
```

### Problème 4: Erreurs après `git pull`

**Cause:** Dépendances non synchronisées ou cache obsolète

**Solution:**

```bash
composer install
php bin/console cache:clear
php bin/console doctrine:migrations:migrate
```

### Problème 5: "Port 8000 already in use"

**Solution:** Utilise un autre port:

```bash
symfony server:start --port=8001
# OU
php -S localhost:8001 -t public
```

---

## 🗃️ Schéma de Base de Données

### Entités Principales

1. **Utilisateur** (4 rôles: admin, enseignant, parent, élève)
2. **Classe** (6ème A, 5ème B, etc.)
3. **Matiere** (Mathématiques, Français, etc.)
4. **Note** (valeur, type, trimestre)
5. **Inscription** (élève → classe par année)
6. **EnseignantMatiereClasse** (qui enseigne quoi où)
7. **EleveParent** (relation familiale)

### Relations

-   Un élève → une inscription par an
-   Un élève → plusieurs notes
-   Un enseignant → plusieurs matières → plusieurs classes
-   Un élève → 1 ou 2 parents

---

## 👥 Contribution

### Créer une Branche de Fonctionnalité

```bash
# Créer et basculer sur une nouvelle branche
git checkout -b feature/nom-de-ta-fonctionnalite   (camelCase)

# Faire tes modifications...

# Commit et push
git add .
git commit -m "feat: Description de la fonctionnalité"
git push origin feature/nom-de-ta-fonctionnalite
```

### Créer une Pull Request

1. Va sur GitHub
2. Clique sur **"Compare & pull request"**
3. Décris tes modifications
4. Demande une review
5. Merge après validation

---

## 📖 Documentation

-   [Symfony Documentation](https://symfony.com/doc/current/index.html)
-   [Doctrine ORM](https://www.doctrine-project.org/projects/doctrine-orm/en/current/index.html)
-   [Twig Templates](https://twig.symfony.com/doc/3.x/)
-   [Git Basics](https://git-scm.com/book/fr/v2)

---

## 📞 Support

Si tu rencontres un problème:

1. ✅ Vérifie la section **"Résolution de Problèmes"** ci-dessus
2. ✅ Consulte les logs: `var/log/dev.log`
3. ✅ Contacte l'équipe via [Discord/Slack/Email]

---

## 📝 License

Ce projet est développé dans le cadre d'un projet académique.

---

## ✨ Auteurs

-   **[Mouhssine]** - Développeur Principal
-   **[Soukaina]** - Développeuse

---

**Bon développement! 🚀**
