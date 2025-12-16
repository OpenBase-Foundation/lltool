import React, { useState, useEffect } from 'react';
import { Plus, Users, Share2, Edit3, Trash2 } from 'lucide-react';
import { supabase } from '../../lib/supabase';
import { Cohort } from '../../lib/database.types';
import { useAuth } from '../../hooks/useAuth';
import { CohortForm } from './CohortForm';
import { ShareCohortModal } from './ShareCohortModal';

interface CohortListProps {
  selectedCohort: Cohort | null;
  onSelectCohort: (cohort: Cohort) => void;
}

export function CohortList({ selectedCohort, onSelectCohort }: CohortListProps) {
  const [cohorts, setCohorts] = useState<Cohort[]>([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [editingCohort, setEditingCohort] = useState<Cohort | null>(null);
  const [shareModalCohort, setShareModalCohort] = useState<Cohort | null>(null);
  const { user } = useAuth();

  useEffect(() => {
    if (user) {
      fetchCohorts();
    }
  }, [user]);

  const fetchCohorts = async () => {
    try {
      const { data, error } = await supabase
        .from('cohorts')
        .select('*')
        .order('created_at', { ascending: false });

      if (error) throw error;
      setCohorts(data || []);
    } catch (error) {
      console.error('Error fetching cohorts:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (cohort: Cohort) => {
    if (!confirm(`Weet je zeker dat je het cohort "${cohort.name}" wilt verwijderen?`)) {
      return;
    }

    try {
      const { error } = await supabase
        .from('cohorts')
        .delete()
        .eq('id', cohort.id);

      if (error) throw error;
      
      setCohorts(cohorts.filter(c => c.id !== cohort.id));
      if (selectedCohort?.id === cohort.id) {
        onSelectCohort(cohorts[0] || null);
      }
    } catch (error) {
      console.error('Error deleting cohort:', error);
    }
  };

  const handleFormSubmit = () => {
    setShowForm(false);
    setEditingCohort(null);
    fetchCohorts();
  };

  if (loading) {
    return (
      <div className="bg-white rounded-lg shadow-sm p-6">
        <div className="animate-pulse space-y-4">
          <div className="h-4 bg-gray-200 rounded w-3/4"></div>
          <div className="h-4 bg-gray-200 rounded w-1/2"></div>
          <div className="h-4 bg-gray-200 rounded w-2/3"></div>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-lg shadow-sm">
      <div className="p-6 border-b border-gray-200">
        <div className="flex justify-between items-center">
          <h2 className="text-lg font-semibold text-gray-900">Cohorten</h2>
          <button
            onClick={() => setShowForm(true)}
            className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            <Plus className="w-4 h-4" />
            Nieuw cohort
          </button>
        </div>
      </div>

      <div className="p-6">
        {cohorts.length === 0 ? (
          <div className="text-center py-8">
            <Users className="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <p className="text-gray-500 mb-4">Nog geen cohorten aangemaakt</p>
            <button
              onClick={() => setShowForm(true)}
              className="text-blue-600 hover:text-blue-800 font-medium"
            >
              Maak je eerste cohort aan
            </button>
          </div>
        ) : (
          <div className="space-y-3">
            {cohorts.map((cohort) => (
              <div
                key={cohort.id}
                className={`p-4 rounded-lg border-2 cursor-pointer transition-all ${
                  selectedCohort?.id === cohort.id
                    ? 'border-blue-500 bg-blue-50'
                    : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
                }`}
                onClick={() => onSelectCohort(cohort)}
              >
                <div className="flex justify-between items-center">
                  <div>
                    <h3 className="font-medium text-gray-900">{cohort.name}</h3>
                    <p className="text-sm text-gray-500">
                      {cohort.owner_id === user?.id ? 'Eigenaar' : 'Gedeeld'}
                    </p>
                  </div>
                  
                  {cohort.owner_id === user?.id && (
                    <div className="flex gap-2">
                      <button
                        onClick={(e) => {
                          e.stopPropagation();
                          setShareModalCohort(cohort);
                        }}
                        className="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                        title="Delen"
                      >
                        <Share2 className="w-4 h-4" />
                      </button>
                      <button
                        onClick={(e) => {
                          e.stopPropagation();
                          setEditingCohort(cohort);
                          setShowForm(true);
                        }}
                        className="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                        title="Bewerken"
                      >
                        <Edit3 className="w-4 h-4" />
                      </button>
                      <button
                        onClick={(e) => {
                          e.stopPropagation();
                          handleDelete(cohort);
                        }}
                        className="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                        title="Verwijderen"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </div>
                  )}
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {showForm && (
        <CohortForm
          cohort={editingCohort}
          onClose={() => {
            setShowForm(false);
            setEditingCohort(null);
          }}
          onSubmit={handleFormSubmit}
        />
      )}

      {shareModalCohort && (
        <ShareCohortModal
          cohort={shareModalCohort}
          onClose={() => setShareModalCohort(null)}
        />
      )}
    </div>
  );
}