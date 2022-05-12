<?php

namespace App\Domain\Academic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Settings\Models\Campus;
use App\Domain\Registration\Models\Student;
use App\Domain\Application\Models\EntryRequirement;
use App\Domain\Application\Models\ApplicantProgramSelection;

class CampusProgram extends Model
{
    use HasFactory;

    protected $table = 'campus_program';

    /**
     * Establish one to many relationship with students
     */
    public function students()
    {
        return $this->hasMany(Student::class,'campus_program_id');
    }

    /**
     * Establish one to many relationship with selections
     */
    public function selections()
    {
        return $this->hasMany(ApplicantProgramSelection::class,'campus_program_id');
    }

     /**
     * Establish one to many relationship with students
     */
    public function entryRequirements()
    {
        return $this->hasMany(EntryRequirement::class,'campus_program_id');
    }

    /**
     * Establish one to many relationship with streams
     */
    public function streams()
    {
        return $this->hasMany(Stream::class,'campus_program_id');
    }

    /**
     * Establish one to many through relationship with groups
     */
    public function groups()
    {
        return $this->hasManyThrough(Group::class,Stream::class);
    }

    /**
     * Establish one to many relationship with programs
     */
    public function program()
    {
    	return $this->belongsTo(Program::class,'program_id');
    }

    /**
     * Establish one to many relationship with campuses
     */
    public function campus()
    {
    	return $this->belongsTo(Campus::class,'campus_id');
    }

    /**
     * Establish many to many relationship with study academic years
     */
    public function studyAcademicYears()
    {
    	return $this->belongsToMany(StudyAcademicYear::class,'study_academic_year_campus_program','campus_program_id','study_academic_year_id');
    }

    /**
     * Establish one to many relationship with program module assignments
     */
    public function programModuleAssignments()
    {
        return $this->hasMany(ProgramModuleAssignment::class,'campus_program_id');
    }

    /**
     * Set regulator code attribute
     */
    public function setRegulatorCodeAttribute($value)
    {
        $this->attributes['regulator_code'] = strtoupper($value);
    }

    /**
     * Get regulator code attribute
     */
    public function getRegulatorCodeAttribute($value)
    {
        return strtoupper($value);
    }

    /**
     * @param string $relation - The relation to create the query for
     * @param string|null $overwrite_table - In case if you want to overwrite the table (join as)
     * @return Builder
     */
    public static function RelationToJoin(string $relation, $overwrite_table = false) {
        $instance = (new self());
        if(!method_exists($instance, $relation))
            throw new \Error('Method ' . $relation . ' does not exists on class ' . self::class);
        $relationData = $instance->{$relation}();
        if(gettype($relationData) !== 'object')
            throw new \Error('Method ' . $relation . ' is not a relation of class ' . self::class);
        if(!is_subclass_of(get_class($relationData), Relation::class))
            throw new \Error('Method ' . $relation . ' is not a relation of class ' . self::class);
        $related = $relationData->getRelated();
        $me = new self();
        $query = $relationData->getQuery()->getQuery();
        switch(get_class($relationData)) {
            case HasOne::class:
                $keys = [
                    'foreign' => $relationData->getForeignKeyName(),
                    'local' => $relationData->getLocalKeyName()
                ];
            break;
            case BelongsTo::class:
                $keys = [
                    'foreign' => $relationData->getOwnerKeyName(),
                    'local' => $relationData->getForeignKeyName()
                ];
            break;
            default:
                throw new \Error('Relation join only works with one to one relationships');
        }
        $checks = [];
        $other_table = ($overwrite_table ? $overwrite_table : $related->getTable());
        foreach($keys as $key) {
            array_push($checks, $key);
            array_push($checks, $related->getTable() . '.' . $key);
        }
        foreach($query->wheres as $key => $where)
            if(in_array($where['type'], ['Null', 'NotNull']) && in_array($where['column'], $checks))
                unset($query->wheres[$key]);
        $query = $query->whereRaw('`' . $other_table . '`.`' . $keys['foreign'] . '` = `' . $me->getTable() . '`.`' . $keys['local'] . '`');
        return (object) [
            'query' => $query,
            'table' => $related->getTable(),
            'wheres' => $query->wheres,
            'bindings' => $query->bindings
        ];
    }

    /**
     * @param Builder $builder
     * @param string $relation - The relation to join
     */
    public function scopeJoinRelation(Builder $query, string $relation) {
        $join_query = self::RelationToJoin($relation, $relation);
        $query->join($join_query->table . ' AS ' . $relation, function(JoinClause $builder) use($join_query) {
            return $builder->mergeWheres($join_query->wheres, $join_query->bindings);
        });
        return $query;
    }
}
