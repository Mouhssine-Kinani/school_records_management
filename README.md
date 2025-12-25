# 🎓 Système de Gestion de Dossiers Scolaires

Application web de gestion scolaire développée avec Symfony 7 et MySQL.

## 📋 Fonctionnalités

- Gestion des élèves, classes et matières
- Gestion des notes et calcul automatique des bulletins
- Consultation des résultats par les parents/élèves
- 4 rôles : Administrateur, Enseignant, Parent, Élève

## 🛠️ Technologies

- **Backend**: Symfony 7 (PHP 8.2+)
- **Base de données**: MySQL 8.0 / MariaDB 10.4+
- **ORM**: Doctrine
- **Gestion des dépendances**: Composer

## ⚙️ Prérequis

Avant de commencer, assure-toi d'avoir installé sur ton ordinateur:

- ✅ [XAMPP](https://www.apachefriends.org/) (avec PHP 8.2+ et MySQL/MariaDB)
- ✅ [Composer](https://getcomposer.org/download/)
- ✅ [Git](https://git-scm.com/downloads)
- ✅ Un éditeur de code (VS Code recommandé)

### Vérifier les versions

Ouvre PowerShell et exécute:
```bash
php -v          # Doit afficher PHP 8.2.x ou supérieur
composer -V     # Doit afficher Composer version 2.x
git --version   # Doit afficher git version 2.x
```

## 📥 Installation du Projet

### Étape 1: Cloner le Projet

Ouvre **PowerShell** et navigue vers le dossier où tu veux installer le projet:
```bash
# Va dans le dossier htdocs de XAMPP
cd C:\xampp\htdocs

# Clone le repository (remplace USERNAME et REPO par les vrais noms)
git clone https://github.com/USERNAME/gestion_dossier_scolaire.git

# Entre dans le dossier du projet
cd gestion_dossier_scolaire
```

### Étape 2: Installer les Dépendances PHP
```bash
composer install
```

⏳ Cette commande peut prendre 2-3 minutes. Elle télécharge toutes les bibliothèques nécessaires.

### Étape 3: Configurer la Base de Données

#### 3.1 Créer le fichier de configuration local
```bash
# Copie le fichier d'exemple
copy .env .env.local
```

#### 3.2 Éditer `.env.local`

Ouvre le fichier `.env.local` avec ton éditeur et modifie la ligne `DATABASE_URL`:
```env
# Si tu utilises XAMPP avec les paramètres par défaut:
DATABASE_URL="mysql://root:@127.0.0.1:3306/school_records_management?serverVersion=mariadb-10.4.32&charset=utf8mb4"
```

**Paramètres à adapter:**
- `root` = ton nom d'utilisateur MySQL (généralement `root` avec XAMPP)
- Après le `:` = ton mot de passe MySQL (vide par défaut avec XAMPP, donc juste `@`)
- `school_records_management` = nom de la base de données
- `mariadb-10.4.32` = ta version de MariaDB (vérifie avec `mysql --version`)

-ou utilise : DATABASE_URL="mysql://root:@127.0.0.1:3306/school_records_management"

#### 3.3 Vérifier que MySQL est démarré

Ouvre le **XAMPP Control Panel** et assure-toi que:
- ✅ **Apache** est démarré (bouton vert "Start")
- ✅ **MySQL** est démarré (bouton vert "Start")

### Étape 4: Créer la Base de Données
```bash
# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations (créer les tables)
php bin/console doctrine:migrations:migrate
```

Réponds **"yes"** quand on te demande confirmation.

### Étape 5: Charger les Données de Test (Fixtures)
```bash
php bin/console doctrine:fixtures:load
```

Réponds **"yes"** pour confirmer. Cette commande va créer:
- 1 administrateur
- ~12 enseignants
- 30 parents
- ~300 élèves
- 12 classes
- 8 matières
- ~4500 notes

### Étape 6: Vider le Cache
```bash
php bin/console cache:clear
```

### Étape 7: Démarrer le Serveur

Tu as **deux options**:

#### Option A: Utiliser le serveur Symfony (Recommandé)
```bash
# Si tu as Symfony CLI installé
symfony server:start
```

Puis ouvre ton navigateur: `http://127.0.0.1:8000`

#### Option B: Utiliser Apache de XAMPP

Ouvre ton navigateur: `http://localhost/gestion_dossier_scolaire/public`

## 🔐 Comptes de Test

Après avoir chargé les fixtures, tu peux te connecter avec:

| Rôle          | Email               | Mot de passe    |
|---------------|---------------------|-----------------|
| Administrateur| admin@school.com    | admin123        |
| Enseignant    | (email généré)      | enseignant123   |
| Parent        | (email généré)      | parent123       |
| Élève         | (email généré)      | eleve123        |

💡 **Astuce**: Consulte la base de données pour voir tous les emails générés:
```bash
C:\xampp\mysql\bin\mysql.exe -u root
USE school_records_management;
SELECT email, role FROM utilisateur LIMIT 20;
```

## 📁 Structure du Projet
```
gestion_dossier_scolaire/
├── config/              # Configuration Symfony
├── migrations/          # Fichiers de migration de la base de données
├── public/              # Point d'entrée web (index.php)
├── src/
│   ├── Controller/      # Contrôleurs
│   ├── Entity/          # Entités Doctrine (modèles)
│   ├── Repository/      # Repositories
│   └── DataFixtures/    # Données de test
├── templates/           # Templates Twig
├── var/                 # Cache et logs
├── .env                 # Configuration (NE PAS MODIFIER)
├── .env.local          # Configuration locale (MODIFIER ICI)
└── composer.json       # Dépendances PHP
```

## 🔄 Workflow Git (Collaboration)

### Avant de Commencer à Travailler
```bash
# Récupère les dernières modifications
git pull origin main
```

### Après avoir fait des Modifications
```bash
# Voir les fichiers modifiés
git status

# Ajouter les fichiers modifiés
git add .

# Créer un commit avec un message descriptif
git commit -m "Description de tes modifications"

# Envoyer vers GitHub
git push origin main
```

### ⚠️ Fichiers à NE JAMAIS Commit

Ces fichiers sont déjà dans `.gitignore`:
- ❌ `.env.local` (configuration locale)
- ❌ `var/cache/` (cache)
- ❌ `var/log/` (logs)
- ❌ `vendor/` (dépendances Composer)

## 🐛 Résolution de Problèmes Courants

### Problème: "could not find driver"

**Solution**: Active les extensions PHP dans `php.ini`:

1. Ouvre `C:\xampp\php\php.ini`
2. Trouve et décommente (enlève le `;`):
```ini
   extension=pdo_mysql
   extension=mysqli
```
3. Redémarre Apache dans XAMPP

### Problème: "Access denied for user"

**Solution**: Vérifie tes identifiants MySQL dans `.env.local`

### Problème: "Table doesn't exist"

**Solution**: Exécute les migrations:
```bash
php bin/console doctrine:migrations:migrate
```

### Problème: Port 80 ou 3306 déjà utilisé

**Solution**: 
- Ferme Skype (utilise le port 80)
- Arrête d'autres serveurs MySQL/PostgreSQL

### Problème: Après un `git pull`, erreur avec Composer

**Solution**: Réinstalle les dépendances:
```bash
composer install
php bin/console cache:clear
```

## 🧪 Commandes Utiles
```bash
# Créer une nouvelle entité
php bin/console make:entity NomEntite

# Créer une migration
php bin/console make:migration

# Créer un contrôleur
php bin/console make:controller NomController

# Créer un formulaire
php bin/console make:form NomFormType

# Créer un CRUD complet
php bin/console make:crud NomEntite

# Vider le cache
php bin/console cache:clear

# Voir toutes les routes
php bin/console debug:router

# Valider le schéma de base de données
php bin/console doctrine:schema:validate
```

## 📚 Documentation Utile

- [Documentation Symfony](https://symfony.com/doc/current/index.html)
- [Documentation Doctrine](https://www.doctrine-project.org/projects/doctrine-orm/en/current/index.html)
- [Guide Git pour Débutants](https://git-scm.com/book/fr/v2)

## 👥 Contribution

1. Crée une branche pour ta fonctionnalité: `git checkout -b feature/ma-fonctionnalite`
2. Commit tes changements: `git commit -m "Ajout de ma fonctionnalité"`
3. Push vers la branche: `git push origin feature/ma-fonctionnalite`
4. Crée une Pull Request sur GitHub

## 📞 Besoin d'Aide?

Si tu rencontres un problème:
1. Vérifie la section "Résolution de Problèmes" ci-dessus
2. Consulte les logs: `var/log/dev.log`
3. Contacte l'équipe sur [Discord/Slack/Email]

## 📝 Licence

Ce projet est développé dans le cadre d'un projet académique.

---

**Bonne chance! 🚀**
