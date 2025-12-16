import React, { useState, useEffect } from 'react';
import { Plus, UserPlus, Download, Filter } from 'lucide-react';
import { supabase } from '../../lib/supabase';
import { Student, Cohort } from '../../lib/database.types';
import { StudentCard } from './StudentCard';
import { StudentForm } from './StudentForm';
import { ExportModal } from './ExportModal';

interface StudentListProps {
  cohort: Cohort;
}

export function StudentList({ cohort }: StudentListProps) {
  const [students, setStudents] = useState<Student[]>([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [editingStudent, setEditingStudent] = useState<Student | null>(null);
  const [showExportModal, setShowExportModal] = useState(false);
  const [filterGroup, setFilterGroup] = useState<'all' | 1 | 2 | 3>('all');

  useEffect(() => {
    fetchStudents();
  }, [cohort.id]);

  const fetchStudents = async () => {
    try {
      const { data, error } = await supabase
        .from('students')
        .select('*')
        .eq('cohort_id', cohort.id)
        .order('name');

      if (error) throw error;
      setStudents(data || []);
    } catch (error) {
      console.error('Error fetching students:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleFormSubmit = () => {
    setShowForm(false);
    setEditingStudent(null);
    fetchStudents();
  };

  const handleEdit = (student: Student) => {
    setEditingStudent(student);
    setShowForm(true);
  };

  const handleDelete = async (student: Student) => {
    if (!confirm(`Weet je zeker dat je ${student.name} wilt verwijderen?`)) {
      return;
    }

    try {
      // Delete photo from storage if it exists
      if (student.photo_url) {
        const fileName = student.photo_url.split('/').pop();
        if (fileName) {
          await supabase.storage
            .from('student_photos')
            .remove([fileName]);
        }
      }

      // Delete student record
      const { error } = await supabase
        .from('students')
        .delete()
        .eq('id', student.id);

      if (error) throw error;
      fetchStudents();
    } catch (error) {
      console.error('Error deleting student:', error);
    }
  };

  const filteredStudents = students.filter(student => 
    filterGroup === 'all' || student.leergroep === filterGroup
  );

  const groupedStudents = {
    1: filteredStudents.filter(s => s.leergroep === 1),
    2: filteredStudents.filter(s => s.leergroep === 2),
    3: filteredStudents.filter(s => s.leergroep === 3),
  };

  if (loading) {
    return (
      <div className="bg-white rounded-lg shadow-sm p-6">
        <div className="animate-pulse space-y-4">
          <div className="h-4 bg-gray-200 rounded w-3/4"></div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {[1, 2, 3, 4, 5, 6].map((i) => (
              <div key={i} className="h-32 bg-gray-200 rounded-lg"></div>
            ))}
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-lg shadow-sm">
      <div className="p-6 border-b border-gray-200">
        <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
          <div>
            <h2 className="text-lg font-semibold text-gray-900">{cohort.name}</h2>
            <p className="text-sm text-gray-600">
              {students.length} leerling{students.length !== 1 ? 'en' : ''}
            </p>
          </div>
          
          <div className="flex gap-3">
            <div className="flex items-center gap-2">
              <Filter className="w-4 h-4 text-gray-400" />
              <select
                value={filterGroup}
                onChange={(e) => setFilterGroup(e.target.value as 'all' | 1 | 2 | 3)}
                className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="all">Alle groepen</option>
                <option value={1}>Leergroep 1</option>
                <option value={2}>Leergroep 2</option>
                <option value={3}>Leergroep 3</option>
              </select>
            </div>
            
            <button
              onClick={() => setShowExportModal(true)}
              className="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
            >
              <Download className="w-4 h-4" />
              Exporteren
            </button>
            
            <button
              onClick={() => setShowForm(true)}
              className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <Plus className="w-4 h-4" />
              Leerling toevoegen
            </button>
          </div>
        </div>
      </div>

      <div className="p-6">
        {students.length === 0 ? (
          <div className="text-center py-12">
            <UserPlus className="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <p className="text-gray-500 mb-4">Nog geen leerlingen toegevoegd</p>
            <button
              onClick={() => setShowForm(true)}
              className="text-blue-600 hover:text-blue-800 font-medium"
            >
              Voeg je eerste leerling toe
            </button>
          </div>
        ) : (
          <div className="space-y-8">
            {filterGroup === 'all' ? (
              // Show grouped by leergroep
              Object.entries(groupedStudents).map(([group, groupStudents]) => (
                groupStudents.length > 0 && (
                  <div key={group}>
                    <h3 className="text-lg font-medium text-gray-900 mb-4">
                      Leergroep {group} ({groupStudents.length} leerlingen)
                    </h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                      {groupStudents.map((student) => (
                        <StudentCard
                          key={student.id}
                          student={student}
                          onEdit={handleEdit}
                          onDelete={handleDelete}
                          onUpdate={fetchStudents}
                        />
                      ))}
                    </div>
                  </div>
                )
              ))
            ) : (
              // Show filtered results
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                {filteredStudents.map((student) => (
                  <StudentCard
                    key={student.id}
                    student={student}
                    onEdit={handleEdit}
                    onDelete={handleDelete}
                    onUpdate={fetchStudents}
                  />
                ))}
              </div>
            )}
          </div>
        )}
      </div>

      {showForm && (
        <StudentForm
          student={editingStudent}
          cohortId={cohort.id}
          onClose={() => {
            setShowForm(false);
            setEditingStudent(null);
          }}
          onSubmit={handleFormSubmit}
        />
      )}

      {showExportModal && (
        <ExportModal
          cohort={cohort}
          students={students}
          onClose={() => setShowExportModal(false)}
        />
      )}
    </div>
  );
}