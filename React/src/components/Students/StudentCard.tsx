import React, { useState } from 'react';
import { Edit3, Trash2, User, ChevronDown } from 'lucide-react';
import { Student } from '../../lib/database.types';
import { supabase } from '../../lib/supabase';

interface StudentCardProps {
  student: Student;
  onEdit: (student: Student) => void;
  onDelete: (student: Student) => void;
  onUpdate: () => void;
}

export function StudentCard({ student, onEdit, onDelete, onUpdate }: StudentCardProps) {
  const [showDropdown, setShowDropdown] = useState(false);
  const [updatingGroup, setUpdatingGroup] = useState(false);

  const handleGroupChange = async (newGroup: 1 | 2 | 3) => {
    setUpdatingGroup(true);
    try {
      const { error } = await supabase
        .from('students')
        .update({ leergroep: newGroup, updated_at: new Date().toISOString() })
        .eq('id', student.id);

      if (error) throw error;
      onUpdate();
    } catch (error) {
      console.error('Error updating student group:', error);
    } finally {
      setUpdatingGroup(false);
      setShowDropdown(false);
    }
  };

  return (
    <div className="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
      <div className="flex flex-col items-center text-center mb-4">
        <div className="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-3 overflow-hidden">
          {student.photo_url ? (
            <img
              src={student.photo_url}
              alt={student.name}
              className="w-full h-full object-cover"
            />
          ) : (
            <User className="w-8 h-8 text-gray-400" />
          )}
        </div>
        <h3 className="font-medium text-gray-900 mb-1">{student.name}</h3>
        
        <div className="relative">
          <button
            onClick={() => setShowDropdown(!showDropdown)}
            disabled={updatingGroup}
            className="flex items-center gap-1 px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-sm hover:bg-blue-100 transition-colors disabled:opacity-50"
          >
            {updatingGroup ? (
              <div className="w-3 h-3 border-2 border-blue-600 border-t-transparent rounded-full animate-spin" />
            ) : (
              <>
                Groep {student.leergroep}
                <ChevronDown className="w-3 h-3" />
              </>
            )}
          </button>
          
          {showDropdown && (
            <div className="absolute top-full left-1/2 transform -translate-x-1/2 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
              {[1, 2, 3].map((group) => (
                <button
                  key={group}
                  onClick={() => handleGroupChange(group as 1 | 2 | 3)}
                  className={`w-full px-4 py-2 text-sm text-left hover:bg-gray-50 first:rounded-t-lg last:rounded-b-lg ${
                    student.leergroep === group ? 'bg-blue-50 text-blue-700' : 'text-gray-700'
                  }`}
                >
                  Groep {group}
                </button>
              ))}
            </div>
          )}
        </div>
      </div>

      <div className="flex justify-center gap-2">
        <button
          onClick={() => onEdit(student)}
          className="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
          title="Bewerken"
        >
          <Edit3 className="w-4 h-4" />
        </button>
        <button
          onClick={() => onDelete(student)}
          className="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
          title="Verwijderen"
        >
          <Trash2 className="w-4 h-4" />
        </button>
      </div>
    </div>
  );
}