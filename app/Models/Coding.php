<?php

/**
 * The Coding model represents a coding task or activity within the CourseMeLy application.
 * It is tied to the `coding` table in the database and utilizes Eloquent ORM for database interaction.
 *
 * Features of this model:
 * - Implements the HasFactory trait for factory-based model generation.
 * - Specifies fillable attributes for mass-assignment protection.
 * - Defines a polymorphic one-to-one relationship to the `Lesson` model.
 * - Automatically casts the 'hints' attribute to an array for simplified data manipulation.
 *
 * @property string $title The title of the coding task.
 * @property string $language The programming language associated with the task.
 * @property array $hints Hints provided for solving the task, stored as an array.
 * @property string|null $sample_code Example or starter code for the task.
 * @property string|null $result_code The expected result code after task completion.
 * @property string|null $solution_code The correct solution code to the task.
 * @property string|null $instruct Additional instructions or information for the task.
 */

namespace App\Models;

use /**
 * Trait HasFactory
 *
 * This trait is included in Laravel's Eloquent model and provides convenient methods
 * for defining and using model factories. Factories are used to create fake data for testing
 * and seeding the database.
 *
 * This is typically included in models where factory methods are required. By leveraging
 * this trait, developers can utilize the built-in factory builder to quickly generate
 * instances of the model with specified attributes or default values.
 *
 * Usage of this trait assumes that the related Factory class is correctly associated
 * with the model it is being used in.
 *
 * Note: When using factories, ensure you have configured the associated factory file
 * in the `database\Factories` namespace according to Laravel's conventions.
 */
    Illuminate\Database\Eloquent\Factories\HasFactory;
use /**
 * Represents an Eloquent model in the Laravel application "CourseMeLy".
 *
 * This class interacts with the MySQL database used by the application.
 * The application relies on a database queue connection for handling queued jobs.
 *
 * Extend this class to define specific Eloquent models for the "CourseMeLy" application.
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Query\Builder
 */
    Illuminate\Database\Eloquent\Model;

/**
 *
 */
class Coding extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'language',
        'hints',
        'sample_code',
        'result_code',
        'solution_code',
        'instruct'
    ];

    public function lessons()
    {
        return $this->morphOne(Lesson::class, 'lessonable');
    }

    protected $casts = [
        'hints' => 'array',
    ];
}
