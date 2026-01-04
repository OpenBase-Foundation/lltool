<?php
declare(strict_types=1);

namespace LLTool\Services;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;
use LLTool\Models\Cohort;
use LLTool\Models\Student;

final class ExportService
{
    /**
     * Export cohort to Word document.
     */
    public function exportCohort(string $cohortId, string $outputPath): bool
    {
        $cohort = Cohort::find($cohortId);
        
        if (!$cohort) {
            return false;
        }

        $students = Student::findByCohort($cohortId);
        
        // Group students by leergroep
        $grouped = [];
        foreach ($students as $student) {
            $grouped[$student->leergroep][] = $student;
        }

        // Create Word document
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Add title
        $section->addText($cohort->name, ['bold' => true, 'size' => 16]);
        $section->addTextBreak(1);

        // Add students by group
        foreach ([1, 2, 3] as $leergroep) {
            if (!isset($grouped[$leergroep])) {
                continue;
            }

            $section->addText("Leergroep {$leergroep}", ['bold' => true, 'size' => 14]);
            $section->addTextBreak(1);

            $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
            
            foreach ($grouped[$leergroep] as $student) {
                $table->addRow();
                
                // Photo cell
                $photoCell = $table->addCell(2000);
                if ($student->photo_url && file_exists($this->getPhotoPath($student->photo_url))) {
                    $photoCell->addImage(
                        $this->getPhotoPath($student->photo_url),
                        ['width' => 100, 'height' => 100]
                    );
                }
                
                // Name cell
                $nameCell = $table->addCell(4000);
                $nameCell->addText($student->name, ['size' => 12]);
            }

            $section->addTextBreak(1);
        }

        // Save document
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($outputPath);

        return file_exists($outputPath);
    }

    /**
     * Get full path to photo file.
     */
    private function getPhotoPath(string $photoUrl): string
    {
        $filename = basename($photoUrl);
        return dirname(__DIR__, 2) . '/storage/photos/' . $filename;
    }
}

