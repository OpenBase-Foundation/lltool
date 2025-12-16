import React, { useState } from 'react';
import { X, Download, FileText } from 'lucide-react';
import { Document, Packer, Paragraph, ImageRun, HeadingLevel, AlignmentType } from 'docx';
import { Cohort, Student } from '../../lib/database.types';

interface ExportModalProps {
  cohort: Cohort;
  students: Student[];
  onClose: () => void;
}

export function ExportModal({ cohort, students, onClose }: ExportModalProps) {
  const [selectedGroup, setSelectedGroup] = useState<'all' | 1 | 2 | 3>('all');
  const [exporting, setExporting] = useState(false);

  const filteredStudents = students.filter(student => 
    selectedGroup === 'all' || student.leergroep === selectedGroup
  );

  const handleExport = async () => {
    setExporting(true);
    try {
      const children = [];

      // Add title
      children.push(
        new Paragraph({
          children: [
            {
              text: `${cohort.name}${selectedGroup !== 'all' ? ` - Leergroep ${selectedGroup}` : ''}`,
              bold: true,
              size: 32,
            }
          ],
          heading: HeadingLevel.HEADING_1,
          alignment: AlignmentType.CENTER,
          spacing: { after: 400 }
        })
      );

      // Add students
      for (const student of filteredStudents) {
        // Add student name
        children.push(
          new Paragraph({
            children: [
              {
                text: student.name,
                bold: true,
                size: 24,
              }
            ],
            spacing: { before: 200, after: 100 }
          })
        );

        // Add student group
        children.push(
          new Paragraph({
            children: [
              {
                text: `Leergroep ${student.leergroep}`,
                size: 20,
              }
            ],
            spacing: { after: 200 }
          })
        );

        // Add photo if available
        if (student.photo_url) {
          try {
            const response = await fetch(student.photo_url);
            const blob = await response.blob();
            const arrayBuffer = await blob.arrayBuffer();
            
            children.push(
              new Paragraph({
                children: [
                  new ImageRun({
                    data: arrayBuffer,
                    transformation: {
                      width: 150,
                      height: 150,
                    },
                  })
                ],
                alignment: AlignmentType.CENTER,
                spacing: { after: 400 }
              })
            );
          } catch (error) {
            console.error('Error loading image for', student.name, error);
            // Add placeholder text if image fails to load
            children.push(
              new Paragraph({
                children: [
                  {
                    text: '[Foto niet beschikbaar]',
                    italics: true,
                    color: '999999'
                  }
                ],
                alignment: AlignmentType.CENTER,
                spacing: { after: 400 }
              })
            );
          }
        } else {
          children.push(
            new Paragraph({
              children: [
                {
                  text: '[Geen foto]',
                  italics: true,
                  color: '999999'
                }
              ],
              alignment: AlignmentType.CENTER,
              spacing: { after: 400 }
            })
          );
        }
      }

      // Create document
      const doc = new Document({
        sections: [{
          properties: {},
          children: children
        }]
      });

      // Generate and download
      const blob = await Packer.toBlob(doc);
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `${cohort.name}${selectedGroup !== 'all' ? `_leergroep_${selectedGroup}` : ''}.docx`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);

      onClose();
    } catch (error) {
      console.error('Error exporting document:', error);
    } finally {
      setExporting(false);
    }
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div className="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div className="flex justify-between items-center p-6 border-b border-gray-200">
          <div className="flex items-center gap-3">
            <FileText className="w-5 h-5 text-green-600" />
            <h3 className="text-lg font-semibold text-gray-900">
              Exporteren naar Word
            </h3>
          </div>
          <button
            onClick={onClose}
            className="text-gray-400 hover:text-gray-600"
          >
            <X className="w-6 h-6" />
          </button>
        </div>

        <div className="p-6">
          <div className="mb-6">
            <h4 className="font-medium text-gray-900 mb-2">{cohort.name}</h4>
            <p className="text-sm text-gray-600">
              Selecteer welke leergroep je wilt exporteren
            </p>
          </div>

          <div className="mb-6">
            <label htmlFor="group-select" className="block text-sm font-medium text-gray-700 mb-2">
              Leergroep
            </label>
            <select
              id="group-select"
              value={selectedGroup}
              onChange={(e) => setSelectedGroup(e.target.value as 'all' | 1 | 2 | 3)}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="all">Alle leergroepen ({students.length} leerlingen)</option>
              <option value={1}>Leergroep 1 ({students.filter(s => s.leergroep === 1).length} leerlingen)</option>
              <option value={2}>Leergroep 2 ({students.filter(s => s.leergroep === 2).length} leerlingen)</option>
              <option value={3}>Leergroep 3 ({students.filter(s => s.leergroep === 3).length} leerlingen)</option>
            </select>
          </div>

          <div className="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-6">
            <p className="text-sm text-blue-700">
              Het Word-document zal {filteredStudents.length} leerling{filteredStudents.length !== 1 ? 'en' : ''} bevatten 
              met hun namen, leergroepen en foto's.
            </p>
          </div>

          <div className="flex gap-3">
            <button
              onClick={onClose}
              className="flex-1 px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
            >
              Annuleren
            </button>
            <button
              onClick={handleExport}
              disabled={exporting || filteredStudents.length === 0}
              className="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-2"
            >
              {exporting ? (
                <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
              ) : (
                <>
                  <Download className="w-4 h-4" />
                  Exporteren
                </>
              )}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}