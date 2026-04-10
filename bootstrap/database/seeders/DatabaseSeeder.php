<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Database seeder to populate the LMS with sample data for testing and development.
 */

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Chapter;
use App\Models\Formation;
use App\Models\Note;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\SubChapter;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        $admin = User::create([
            'name' => 'Admin LMS',
            'email' => 'admin@lms.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $student1 = User::create([
            'name' => 'Jean Dupont',
            'email' => 'student@lms.test',
            'password' => Hash::make('password'),
            'role' => 'apprenant',
        ]);

        $student2 = User::create([
            'name' => 'Marie Lambert',
            'email' => 'marie@lms.test',
            'password' => Hash::make('password'),
            'role' => 'apprenant',
        ]);

        // Formation 1: English Irregular Verbs
        $formation1 = Formation::create([
            'name' => 'Les verbes irréguliers en anglais',
            'description' => 'Apprenez les verbes irréguliers les plus courants en anglais. Ce cours couvre les formes au prétérit et au participe passé, avec des exemples d\'utilisation en contexte.',
            'level' => 'débutant',
            'duration_hours' => 8,
            'status' => 'published',
        ]);

        // Chapter 1: Introduction
        $ch1 = Chapter::create([
            'formation_id' => $formation1->id,
            'title' => 'Introduction aux verbes irréguliers',
            'description' => 'Comprendre ce qu\'est un verbe irrégulier et pourquoi ils sont importants.',
            'order' => 1,
        ]);

        $sub1_1 = SubChapter::create([
            'chapter_id' => $ch1->id,
            'title' => 'Qu\'est-ce qu\'un verbe irrégulier ?',
            'order' => 1,
            'content' => '<h3>Définition</h3>
<p>En anglais, un <strong>verbe irrégulier</strong> est un verbe dont la forme au <em>prétérit</em> (past simple) et/ou au <em>participe passé</em> (past participle) ne suit pas la règle standard d\'ajout de <code>-ed</code>.</p>

<h3>Verbes réguliers vs irréguliers</h3>
<p>La plupart des verbes anglais sont <strong>réguliers</strong> : on ajoute simplement <code>-ed</code> pour former le passé.</p>
<ul>
    <li><strong>walk</strong> → walked → walked</li>
    <li><strong>play</strong> → played → played</li>
</ul>

<p>Les verbes <strong>irréguliers</strong> changent de forme de manière imprévisible :</p>
<ul>
    <li><strong>go</strong> → went → gone</li>
    <li><strong>see</strong> → saw → seen</li>
    <li><strong>take</strong> → took → taken</li>
</ul>

<h3>Pourquoi les apprendre ?</h3>
<p>Il existe environ <strong>200 verbes irréguliers</strong> couramment utilisés en anglais. Les maîtriser est essentiel pour parler et écrire correctement. La bonne nouvelle : les 50 plus fréquents couvrent la grande majorité des situations quotidiennes.</p>',
        ]);

        $sub1_2 = SubChapter::create([
            'chapter_id' => $ch1->id,
            'title' => 'Les trois formes verbales',
            'order' => 2,
            'content' => '<h3>Les trois colonnes</h3>
<p>Chaque verbe irrégulier se décline en <strong>trois formes</strong> :</p>
<ul>
    <li><strong>Base form</strong> (infinitif) : la forme de base du verbe</li>
    <li><strong>Past simple</strong> (prétérit) : utilisé pour les actions passées terminées</li>
    <li><strong>Past participle</strong> (participe passé) : utilisé avec les auxiliaires have/has/had</li>
</ul>

<h3>Exemples</h3>
<table>
    <tr><th>Base</th><th>Past Simple</th><th>Past Participle</th><th>Traduction</th></tr>
    <tr><td>be</td><td>was/were</td><td>been</td><td>être</td></tr>
    <tr><td>have</td><td>had</td><td>had</td><td>avoir</td></tr>
    <tr><td>do</td><td>did</td><td>done</td><td>faire</td></tr>
    <tr><td>say</td><td>said</td><td>said</td><td>dire</td></tr>
    <tr><td>get</td><td>got</td><td>got/gotten</td><td>obtenir</td></tr>
</table>

<h3>Astuce de mémorisation</h3>
<p>Regroupez les verbes par <em>modèle de changement</em>. Par exemple, certains verbes ne changent pas du tout (cut → cut → cut), d\'autres changent une seule voyelle (sing → sang → sung).</p>',
        ]);

        // Chapter 2: 10 Essential Verbs
        $ch2 = Chapter::create([
            'formation_id' => $formation1->id,
            'title' => '10 verbes indispensables',
            'description' => 'Les verbes irréguliers les plus fréquents à connaître absolument.',
            'order' => 2,
        ]);

        $sub2_1 = SubChapter::create([
            'chapter_id' => $ch2->id,
            'title' => 'Groupe 1 : be, have, do, go, say',
            'order' => 1,
            'content' => '<h3>Les 5 verbes les plus utilisés</h3>

<p><strong>1. BE — was/were — been</strong> (être)</p>
<ul>
    <li>I <em>was</em> happy yesterday. (J\'étais content hier.)</li>
    <li>She has <em>been</em> to Paris. (Elle est allée à Paris.)</li>
</ul>

<p><strong>2. HAVE — had — had</strong> (avoir)</p>
<ul>
    <li>We <em>had</em> a great time. (Nous avons passé un bon moment.)</li>
    <li>I have <em>had</em> enough. (J\'en ai assez.)</li>
</ul>

<p><strong>3. DO — did — done</strong> (faire)</p>
<ul>
    <li>She <em>did</em> her homework. (Elle a fait ses devoirs.)</li>
    <li>The work is <em>done</em>. (Le travail est fait.)</li>
</ul>

<p><strong>4. GO — went — gone</strong> (aller)</p>
<ul>
    <li>They <em>went</em> to school. (Ils sont allés à l\'école.)</li>
    <li>He has <em>gone</em> home. (Il est rentré chez lui.)</li>
</ul>

<p><strong>5. SAY — said — said</strong> (dire)</p>
<ul>
    <li>He <em>said</em> hello. (Il a dit bonjour.)</li>
    <li>It is <em>said</em> that... (On dit que...)</li>
</ul>',
        ]);

        $sub2_2 = SubChapter::create([
            'chapter_id' => $ch2->id,
            'title' => 'Groupe 2 : get, make, know, take, see',
            'order' => 2,
            'content' => '<h3>5 autres verbes essentiels</h3>

<p><strong>6. GET — got — got/gotten</strong> (obtenir, recevoir)</p>
<ul>
    <li>I <em>got</em> a letter. (J\'ai reçu une lettre.)</li>
</ul>

<p><strong>7. MAKE — made — made</strong> (faire, fabriquer)</p>
<ul>
    <li>She <em>made</em> a cake. (Elle a fait un gâteau.)</li>
</ul>

<p><strong>8. KNOW — knew — known</strong> (savoir, connaître)</p>
<ul>
    <li>I <em>knew</em> the answer. (Je connaissais la réponse.)</li>
</ul>

<p><strong>9. TAKE — took — taken</strong> (prendre)</p>
<ul>
    <li>He <em>took</em> the bus. (Il a pris le bus.)</li>
</ul>

<p><strong>10. SEE — saw — seen</strong> (voir)</p>
<ul>
    <li>We <em>saw</em> a movie. (Nous avons vu un film.)</li>
    <li>I have <em>seen</em> it before. (Je l\'ai déjà vu.)</li>
</ul>',
        ]);

        // Chapter 3: Advanced patterns
        $ch3 = Chapter::create([
            'formation_id' => $formation1->id,
            'title' => 'Modèles et astuces',
            'description' => 'Regrouper les verbes par modèle pour mieux les mémoriser.',
            'order' => 3,
        ]);

        $sub3_1 = SubChapter::create([
            'chapter_id' => $ch3->id,
            'title' => 'Verbes qui ne changent pas',
            'order' => 1,
            'content' => '<h3>Les verbes invariables</h3>
<p>Certains verbes ont la même forme aux trois temps. Ce sont les plus faciles !</p>
<ul>
    <li><strong>cut</strong> → cut → cut (couper)</li>
    <li><strong>put</strong> → put → put (mettre)</li>
    <li><strong>let</strong> → let → let (laisser)</li>
    <li><strong>shut</strong> → shut → shut (fermer)</li>
    <li><strong>hurt</strong> → hurt → hurt (blesser)</li>
    <li><strong>cost</strong> → cost → cost (coûter)</li>
</ul>
<p><em>Conseil : ces verbes se terminent souvent par -t ou -d.</em></p>',
        ]);

        // Quiz for sub2_2 (the 10 essential verbs)
        $quiz1 = Quiz::create([
            'sub_chapter_id' => $sub2_1->id,
            'title' => 'Quiz — Les 5 verbes essentiels',
            'description' => 'Testez vos connaissances sur be, have, do, go et say.',
            'status' => 'published',
        ]);

        $this->createQuestion($quiz1, 'Quel est le prétérit de "go" ?', [
            ['text' => 'goed', 'correct' => false],
            ['text' => 'went', 'correct' => true],
            ['text' => 'gone', 'correct' => false],
        ], 1);

        $this->createQuestion($quiz1, 'Quel est le participe passé de "do" ?', [
            ['text' => 'did', 'correct' => false],
            ['text' => 'doed', 'correct' => false],
            ['text' => 'done', 'correct' => true],
        ], 2);

        $this->createQuestion($quiz1, 'Complétez : She ___ a great teacher. (be, past)', [
            ['text' => 'was', 'correct' => true],
            ['text' => 'been', 'correct' => false],
            ['text' => 'beed', 'correct' => false],
        ], 3);

        $this->createQuestion($quiz1, 'Quel est le prétérit de "have" ?', [
            ['text' => 'haved', 'correct' => false],
            ['text' => 'has', 'correct' => false],
            ['text' => 'had', 'correct' => true],
        ], 4);

        $this->createQuestion($quiz1, 'Complétez : He ___ goodbye. (say, past)', [
            ['text' => 'sayed', 'correct' => false],
            ['text' => 'said', 'correct' => true],
            ['text' => 'sayd', 'correct' => false],
        ], 5);

        // Quiz 2 for sub2_2
        $quiz2 = Quiz::create([
            'sub_chapter_id' => $sub2_2->id,
            'title' => 'Quiz — 5 autres verbes essentiels',
            'description' => 'Testez vos connaissances sur get, make, know, take et see.',
            'status' => 'published',
        ]);

        $this->createQuestion($quiz2, 'Quel est le prétérit de "see" ?', [
            ['text' => 'seed', 'correct' => false],
            ['text' => 'seen', 'correct' => false],
            ['text' => 'saw', 'correct' => true],
        ], 1);

        $this->createQuestion($quiz2, 'Quel est le participe passé de "take" ?', [
            ['text' => 'taken', 'correct' => true],
            ['text' => 'took', 'correct' => false],
            ['text' => 'taked', 'correct' => false],
        ], 2);

        $this->createQuestion($quiz2, 'Complétez : She ___ a cake yesterday. (make, past)', [
            ['text' => 'maked', 'correct' => false],
            ['text' => 'made', 'correct' => true],
            ['text' => 'maken', 'correct' => false],
        ], 3);

        $this->createQuestion($quiz2, 'Quel est le prétérit de "know" ?', [
            ['text' => 'knowed', 'correct' => false],
            ['text' => 'known', 'correct' => false],
            ['text' => 'knew', 'correct' => true],
        ], 4);

        $this->createQuestion($quiz2, 'Complétez : I ___ a letter. (get, past)', [
            ['text' => 'getted', 'correct' => false],
            ['text' => 'got', 'correct' => true],
            ['text' => 'gotten', 'correct' => false],
        ], 5);

        // Formation 2: Introduction to HTML
        $formation2 = Formation::create([
            'name' => 'Introduction au HTML',
            'description' => 'Découvrez les bases du HTML pour créer vos premières pages web.',
            'level' => 'débutant',
            'duration_hours' => 6,
            'status' => 'published',
        ]);

        $ch4 = Chapter::create([
            'formation_id' => $formation2->id,
            'title' => 'Les bases du HTML',
            'description' => 'Structure d\'une page, balises essentielles.',
            'order' => 1,
        ]);

        SubChapter::create([
            'chapter_id' => $ch4->id,
            'title' => 'Structure d\'une page HTML',
            'order' => 1,
            'content' => '<h3>La structure de base</h3>
<p>Toute page HTML commence par une <strong>déclaration DOCTYPE</strong> suivie de la balise <code>&lt;html&gt;</code>.</p>
<p>Le document se divise en deux parties principales :</p>
<ul>
    <li><code>&lt;head&gt;</code> : contient les métadonnées (titre, encodage, liens CSS)</li>
    <li><code>&lt;body&gt;</code> : contient le contenu visible de la page</li>
</ul>
<p>Les balises les plus courantes sont : <code>&lt;h1&gt;</code> à <code>&lt;h6&gt;</code> pour les titres, <code>&lt;p&gt;</code> pour les paragraphes, <code>&lt;a&gt;</code> pour les liens, et <code>&lt;img&gt;</code> pour les images.</p>',
        ]);

        // Enroll students
        $formation1->students()->attach($student1->id, ['enrolled_at' => now()]);
        $formation1->students()->attach($student2->id, ['enrolled_at' => now()]);
        $formation2->students()->attach($student1->id, ['enrolled_at' => now()]);

        // Notes (grades)
        Note::create([
            'user_id' => $student1->id,
            'formation_id' => $formation1->id,
            'subject' => 'Examen mi-parcours',
            'grade' => 14.5,
            'comment' => 'Bon travail, continuez vos efforts.',
        ]);

        Note::create([
            'user_id' => $student1->id,
            'formation_id' => $formation1->id,
            'subject' => 'Participation orale',
            'grade' => 16,
        ]);

        Note::create([
            'user_id' => $student2->id,
            'formation_id' => $formation1->id,
            'subject' => 'Examen mi-parcours',
            'grade' => 11,
            'comment' => 'Des progrès à faire sur les verbes du groupe 2.',
        ]);

        // Sample todos
        Todo::create(['user_id' => $student1->id, 'title' => 'Réviser les verbes irréguliers', 'due_date' => now()->addDays(3)]);
        Todo::create(['user_id' => $student1->id, 'title' => 'Compléter le quiz du chapitre 2', 'due_date' => now()->addDay()]);
        Todo::create(['user_id' => $student1->id, 'title' => 'Lire le sous-chapitre sur le HTML', 'is_completed' => true]);
    }

    private function createQuestion(Quiz $quiz, string $text, array $answers, int $order): void
    {
        $question = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => $text,
            'order' => $order,
        ]);

        foreach ($answers as $answer) {
            Answer::create([
                'question_id' => $question->id,
                'answer_text' => $answer['text'],
                'is_correct' => $answer['correct'],
            ]);
        }
    }
}
