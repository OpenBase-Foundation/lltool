import React, { useState, useEffect } from 'react';
import { X, Share2, Mail, Trash2, UserPlus } from 'lucide-react';
import { supabase } from '../../lib/supabase';
import { Cohort, CohortAccess } from '../../lib/database.types';

interface ShareCohortModalProps {
  cohort: Cohort;
  onClose: () => void;
}

export function ShareCohortModal({ cohort, onClose }: ShareCohortModalProps) {
  const [email, setEmail] = useState('');
  const [permissions, setPermissions] = useState<'view' | 'edit'>('view');
  const [access, setAccess] = useState<(CohortAccess & { user_email?: string })[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchAccess();
  }, [cohort.id]);

  const fetchAccess = async () => {
    try {
      const { data, error } = await supabase
        .from('cohort_access')
        .select(`
          *,
          user_email:user_id (email)
        `)
        .eq('cohort_id', cohort.id);

      if (error) throw error;
      setAccess(data || []);
    } catch (err) {
      console.error('Error fetching access:', err);
    }
  };

  const handleShare = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      // First check if user exists
      const { data: userData, error: userError } = await supabase.auth.admin.listUsers();
      
      if (userError) {
        // Fallback: try to get user by email from auth.users
        const { data: authData, error: authError } = await supabase
          .from('auth.users')
          .select('id')
          .eq('email', email)
          .single();

        if (authError) {
          throw new Error('Gebruiker niet gevonden');
        }

        const userId = authData.id;
        
        const { error: insertError } = await supabase
          .from('cohort_access')
          .insert([{
            cohort_id: cohort.id,
            user_id: userId,
            permissions
          }]);

        if (insertError) throw insertError;
      } else {
        const user = userData.users.find(u => u.email === email);
        if (!user) {
          throw new Error('Gebruiker niet gevonden');
        }

        const { error: insertError } = await supabase
          .from('cohort_access')
          .insert([{
            cohort_id: cohort.id,
            user_id: user.id,
            permissions
          }]);

        if (insertError) throw insertError;
      }

      setEmail('');
      setPermissions('view');
      fetchAccess();
    } catch (err: any) {
      setError(err.message || 'Er is een fout opgetreden');
    } finally {
      setLoading(false);
    }
  };

  const handleRemoveAccess = async (accessId: string) => {
    try {
      const { error } = await supabase
        .from('cohort_access')
        .delete()
        .eq('id', accessId);

      if (error) throw error;
      fetchAccess();
    } catch (err) {
      console.error('Error removing access:', err);
    }
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div className="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div className="flex justify-between items-center p-6 border-b border-gray-200">
          <div className="flex items-center gap-3">
            <Share2 className="w-5 h-5 text-blue-600" />
            <h3 className="text-lg font-semibold text-gray-900">
              Cohort delen
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
              Deel dit cohort met andere gebruikers
            </p>
          </div>

          <form onSubmit={handleShare} className="space-y-4">
            <div>
              <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
                E-mailadres
              </label>
              <div className="relative">
                <Mail className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                <input
                  id="email"
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="gebruiker@email.com"
                  required
                />
              </div>
            </div>

            <div>
              <label htmlFor="permissions" className="block text-sm font-medium text-gray-700 mb-2">
                Rechten
              </label>
              <select
                id="permissions"
                value={permissions}
                onChange={(e) => setPermissions(e.target.value as 'view' | 'edit')}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="view">Alleen bekijken</option>
                <option value="edit">Bekijken en bewerken</option>
              </select>
            </div>

            {error && (
              <div className="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
                {error}
              </div>
            )}

            <button
              type="submit"
              disabled={loading}
              className="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-2"
            >
              {loading ? (
                <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
              ) : (
                <>
                  <UserPlus className="w-4 h-4" />
                  Delen
                </>
              )}
            </button>
          </form>

          {access.length > 0 && (
            <div className="mt-6 pt-6 border-t border-gray-200">
              <h4 className="font-medium text-gray-900 mb-3">Gedeeld met</h4>
              <div className="space-y-2">
                {access.map((item) => (
                  <div key={item.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                      <p className="text-sm font-medium text-gray-900">
                        {item.user_email || 'Onbekende gebruiker'}
                      </p>
                      <p className="text-xs text-gray-500">
                        {item.permissions === 'view' ? 'Alleen bekijken' : 'Bekijken en bewerken'}
                      </p>
                    </div>
                    <button
                      onClick={() => handleRemoveAccess(item.id)}
                      className="text-gray-400 hover:text-red-600 p-1"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}