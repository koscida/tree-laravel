<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LookupDataSeeder extends Seeder
{
    /**
     * Relationship keys that describe more than one person.
     *
     * @var list<string>
     */
    private const MULTIPLE_PEOPLE_RELATIONSHIPS = [
        'parents',
        'groom_parents',
        'bride_parents',
        'godparents',
        'witnesses',
        'survivors',
    ];

    /**
     * @var array<string, string>
     */
    private const RELATIONSHIPS = [
        'child' => 'Child',
        'groom' => 'Groom',
        'bride' => 'Bride',
        'deceased' => 'Deceased',
        'parents' => 'Parents',
        'father' => 'Father',
        'step_father' => 'Step-Father',
        'paternal_grandfather' => 'Paternal Grandfather',
        'paternal_grandmother' => 'Paternal Grandmother',
        'groom_parents' => 'Groom Parents',
        'groom_father' => 'Groom Father',
        'groom_mother' => 'Groom Mother',
        'groom_paternal_grandfather' => 'Groom Paternal Grandfather',
        'groom_paternal_grandmother' => 'Groom Paternal Grandmother',
        'groom_maternal_grandfather' => 'Groom Maternal Grandfather',
        'groom_maternal_grandmother' => 'Groom Maternal Grandmother',
        'mother' => 'Mother',
        'step_mother' => 'Step Mother',
        'maternal_grandfather' => 'Maternal Grandfather',
        'maternal_grandmother' => 'Maternal Grandmother',
        'bride_parents' => 'Bride Parents',
        'bride_father' => 'Bride Father',
        'bride_mother' => 'Bride Mother',
        'bride_paternal_grandfather' => 'Bride Paternal Grandfather',
        'bride_paternal_grandmother' => 'Bride Paternal Grandmother',
        'bride_maternal_grandfather' => 'Bride Maternal Grandfather',
        'bride_maternal_grandmother' => 'Bride Maternal Grandmother',
        'godparents' => 'Godparents',
        'godfather' => 'Godfather',
        'godmother' => 'Godmother',
        'priest' => 'Priest',
        'burial_officiant' => 'Burial officiant',
        'witnesses' => 'Witnesses',
        'witness' => 'Witness',
        'survivors' => 'Survivors',
        'survivor' => 'Survivor',
    ];

    /**
     * @var array<string, string>
     */
    private const NAME_PARTS = [
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'last_name_standard' => 'Last Name Standard',
        'maiden_name' => 'Maiden Name',
        'full_name' => 'Full Name',
        'full_name_standard' => 'Full Name Standard',
        'combination_name' => 'Combination Name',
    ];

    /**
     * @var array<string, string>
     */
    private const MILESTONES = [
        'birth' => 'Birth',
        'baptism' => 'Baptism',
        'marriage' => 'Marriage',
        'death' => 'Death',
        'burial' => 'Burial',
    ];

    /**
     * @var array<string, string>
     */
    private const DATE_PARTS = [
        'day' => 'Day',
        'month' => 'Month',
        'year' => 'Year',
        'whole' => 'Whole',
        'partial' => 'Partial',
    ];

    /**
     * @var array<string, string>
     */
    private const SOURCE_PARTS = [
        'record_type' => 'Record Type',
        'record_name' => 'Record Name',
        'provided_by' => 'Provided By',
        'source_file' => 'Source File',
        'source_file_accessed' => 'Source File Accessed',
        'site_name' => 'Site Name',
        'site_url' => 'Site URL',
        'site_url_accessed' => 'Site URL Accessed',
        'suggested_citation' => 'Suggested Citation',
        'markup_citation' => 'Markup Citation',
    ];

    /**
     * @var array<string, array{label: string, data_format: string}>
     */
    private const RECORD_LABELS = [
        'is_illegitimate' => ['label' => 'Is Illegitimate', 'data_format' => 'boolean'],
        'godmother_1_is_child' => ['label' => 'Godmother 1 is Child', 'data_format' => 'boolean'],
        'godparents_1_is_married' => ['label' => 'Godparents 1 is Married', 'data_format' => 'boolean'],
        'godmother_2_is_child' => ['label' => 'Godmother 2 is Child', 'data_format' => 'boolean'],
        'godparents_2_is_married' => ['label' => 'Godparents 2 is Married', 'data_format' => 'boolean'],
        'line_no' => ['label' => 'Line No', 'data_format' => 'number_string'],
    ];

    public function run(): void
    {
        $this->upsert('relationships', $this->relationshipRows(), ['label', 'is_multiple_people']);
        $this->upsert('name_parts', $this->keyLabelRows(self::NAME_PARTS), ['label']);
        $this->upsert('milestones', $this->keyLabelRows(self::MILESTONES), ['label']);
        $this->upsert('date_parts', $this->keyLabelRows(self::DATE_PARTS), ['label']);
        $this->upsert('source_parts', $this->keyLabelRows(self::SOURCE_PARTS), ['label']);
        $this->upsert('record_labels', $this->recordLabelRows(), ['label', 'data_format']);
    }

    /**
     * @return list<array{key: string, label: string, is_multiple_people: bool}>
     */
    private function relationshipRows(): array
    {
        return array_map(
            fn (string $key, string $label): array => [
                'key' => $key,
                'label' => $label,
                'is_multiple_people' => in_array($key, self::MULTIPLE_PEOPLE_RELATIONSHIPS, true),
            ],
            array_keys(self::RELATIONSHIPS),
            array_values(self::RELATIONSHIPS),
        );
    }

    /**
     * @param  array<string, string>  $rows
     * @return list<array{key: string, label: string}>
     */
    private function keyLabelRows(array $rows): array
    {
        return array_map(
            fn (string $key, string $label): array => ['key' => $key, 'label' => $label],
            array_keys($rows),
            array_values($rows),
        );
    }

    /**
     * @return list<array{key: string, label: string, data_format: string}>
     */
    private function recordLabelRows(): array
    {
        return array_map(
            fn (string $key, array $row): array => [
                'key' => $key,
                'label' => $row['label'],
                'data_format' => $row['data_format'],
            ],
            array_keys(self::RECORD_LABELS),
            array_values(self::RECORD_LABELS),
        );
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  list<string>  $update
     */
    private function upsert(string $table, array $rows, array $update): void
    {
        $timestamp = now();

        DB::table($table)->upsert(
            array_map(fn (array $row): array => $row + [
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], $rows),
            ['key'],
            [...$update, 'updated_at'],
        );
    }
}
