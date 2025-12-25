<?php

namespace App\DataFixtures;

use App\Entity\Classe;
use App\Entity\EleveParent;
use App\Entity\EnseignantMatiereClasse;
use App\Entity\Inscription;
use App\Entity\Matiere;
use App\Entity\Note;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $anneeScolaire = '2024-2025';

        // ========================================
        // 1. CRÉER L'ADMINISTRATEUR
        // ========================================
        $admin = new Utilisateur();
        $admin->setNom('Admin')
            ->setPrenom('Super')
            ->setEmail('admin@school.com')
            ->setMotDePasse($this->passwordHasher->hashPassword($admin, 'admin123'))
            ->setRole('administrateur');
        $manager->persist($admin);

        // ========================================
        // 2. CRÉER LES MATIÈRES
        // ========================================
        $matieres = [];
        $matieresData = [
            ['Mathématiques', 3.0, 4],
            ['Français', 3.0, 4],
            ['Anglais', 2.0, 3],
            ['Sciences Physiques', 2.5, 3],
            ['SVT', 2.0, 3],
            ['Histoire-Géographie', 2.0, 3],
            ['EPS', 1.0, 2],
            ['Arts Plastiques', 1.0, 2],
        ];

        foreach ($matieresData as [$libelle, $coef, $nbr]) {
            $matiere = new Matiere();
            $matiere->setLibelle($libelle)
                ->setCoefficient($coef)
                ->setNbrControle($nbr);
            $manager->persist($matiere);
            $matieres[] = $matiere;
        }

        // ========================================
        // 3. CRÉER LES CLASSES
        // ========================================
        $classes = [];
        $niveaux = ['6ème', '5ème', '4ème', '3ème'];
        $sections = ['A', 'B', 'C'];

        foreach ($niveaux as $niveau) {
            foreach ($sections as $section) {
                $classe = new Classe();
                $classe->setNom($niveau . ' ' . $section)
                    ->setNiveau($niveau)
                    ->setAnneeScolaire($anneeScolaire);
                $manager->persist($classe);
                $classes[] = $classe;
            }
        }

        // ========================================
        // 4. CRÉER LES ENSEIGNANTS
        // ========================================
        $enseignants = [];
        $specialites = ['Mathématiques', 'Lettres', 'Sciences', 'Langues', 'Histoire', 'EPS'];

        foreach ($specialites as $specialite) {
            for ($i = 0; $i < 2; $i++) {
                $enseignant = new Utilisateur();
                $enseignant->setNom($faker->lastName())
                    ->setPrenom($faker->firstName())
                    ->setEmail($faker->unique()->email())
                    ->setMotDePasse($this->passwordHasher->hashPassword($enseignant, 'enseignant123'))
                    ->setRole('enseignant')
                    ->setSpecialite($specialite);
                $manager->persist($enseignant);
                $enseignants[] = $enseignant;
            }
        }

        // ========================================
        // 5. ASSIGNER ENSEIGNANTS -> MATIÈRES -> CLASSES
        // ========================================
        foreach ($classes as $classe) {
            foreach ($matieres as $matiere) {
                // Choisir un enseignant aléatoire pour cette matière
                $enseignant = $enseignants[array_rand($enseignants)];

                $emc = new EnseignantMatiereClasse();
                $emc->setEnseignant($enseignant)
                    ->setMatiere($matiere)
                    ->setClasse($classe)
                    ->setAnneeScolaire($anneeScolaire);
                $manager->persist($emc);
            }
        }

        // ========================================
        // 6. CRÉER LES PARENTS
        // ========================================
        $parents = [];
        for ($i = 0; $i < 30; $i++) {
            $parent = new Utilisateur();
            $parent->setNom($faker->lastName())
                ->setPrenom($faker->firstName())
                ->setEmail($faker->unique()->email())
                ->setMotDePasse($this->passwordHasher->hashPassword($parent, 'parent123'))
                ->setRole('parent')
                ->setTelephone($faker->phoneNumber())
                ->setAdresse($faker->address());
            $manager->persist($parent);
            $parents[] = $parent;
        }

        // ========================================
        // 7. CRÉER LES ÉLÈVES ET INSCRIPTIONS
        // ========================================
        $eleves = [];
        $compteurInscription = 1000;

        foreach ($classes as $classe) {
            // 20-25 élèves par classe
            $nombreEleves = rand(20, 25);

            for ($i = 0; $i < $nombreEleves; $i++) {
                $eleve = new Utilisateur();
                $eleve->setNom($faker->lastName())
                    ->setPrenom($faker->firstName())
                    ->setEmail($faker->unique()->email())
                    ->setMotDePasse($this->passwordHasher->hashPassword($eleve, 'eleve123'))
                    ->setRole('eleve')
                    ->setNumeroInscription('INS' . $compteurInscription++)
                    ->setDateNaissance($faker->dateTimeBetween('-16 years', '-10 years'))
                    ->setLieuNaissance($faker->city());
                $manager->persist($eleve);
                $eleves[] = $eleve;

                // Créer l'inscription pour cet élève
                $inscription = new Inscription();
                $inscription->setEleve($eleve)
                    ->setClasse($classe)
                    ->setAnneeScolaire($anneeScolaire)
                    ->setDateInscription(new \DateTime('2024-09-01'))
                    ->setStatut('active');
                $manager->persist($inscription);

                // ========================================
                // 8. LIER ÉLÈVE -> PARENTS
                // ========================================
                // Choisir 1 ou 2 parents aléatoires pour cet élève
                $nombreParents = rand(1, 2);
                $parentsChoisis = (array) array_rand($parents, $nombreParents);

                foreach ($parentsChoisis as $index => $parentIndex) {
                    $eleveParent = new EleveParent();
                    $eleveParent->setEleve($eleve)
                        ->setParent($parents[$parentIndex])
                        ->setRelation($index === 0 ? 'pere' : 'mere');
                    $manager->persist($eleveParent);
                }

                // ========================================
                // 9. CRÉER DES NOTES POUR CET ÉLÈVE
                // ========================================
                foreach ($matieres as $matiere) {
                    // Trouver l'enseignant qui enseigne cette matière dans cette classe
                    $enseignantMatiere = $enseignants[array_rand($enseignants)];

                    // Générer des notes pour les trimestres 1 et 2
                    for ($trimestre = 1; $trimestre <= 2; $trimestre++) {
                        // 3-5 notes par matière par trimestre
                        $nombreNotes = rand(3, 5);

                        for ($n = 0; $n < $nombreNotes; $n++) {
                            $note = new Note();
                            $note->setEleve($eleve)
                                ->setMatiere($matiere)
                                ->setEnseignant($enseignantMatiere)
                                ->setValeur($faker->randomFloat(2, 0, 20))
                                ->setType($faker->randomElement(['controle', 'devoir', 'examen']))
                                ->setTrimestre((string)$trimestre)
                                ->setAnneeScolaire($anneeScolaire)
                                ->setDateNote($faker->dateTimeBetween('-3 months', 'now'))
                                ->setCommentaire($faker->optional(0.3)->sentence());
                            $manager->persist($note);
                        }
                    }
                }
            }
        }

        // ========================================
        // SAUVEGARDER TOUT
        // ========================================
        $manager->flush();

        echo "\n✅ Fixtures chargées avec succès!\n";
        echo "📊 Statistiques:\n";
        echo "   - 1 administrateur\n";
        echo "   - " . count($enseignants) . " enseignants\n";
        echo "   - " . count($parents) . " parents\n";
        echo "   - " . count($eleves) . " élèves\n";
        echo "   - " . count($classes) . " classes\n";
        echo "   - " . count($matieres) . " matières\n";
        echo "\n🔐 Identifiants par défaut:\n";
        echo "   Admin: admin@school.com / admin123\n";
        echo "   Enseignant: [email généré] / enseignant123\n";
        echo "   Parent: [email généré] / parent123\n";
        echo "   Élève: [email généré] / eleve123\n\n";
    }
}
