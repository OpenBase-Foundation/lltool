import React, { useState, useEffect } from 'react';
import { X, Save, User, Upload, Crop } from 'lucide-react';
import { supabase } from '../../lib/supabase';
import { Student } from '../../lib/database.types';
import { ImageCropper } from './ImageCropper';

interface StudentFormProps {
  student?: Student | null;
  cohortId: string;
  onClose: () => void;
  onSubmit: () => void;
}

export function StudentForm({ student, cohortId, onClose, onSubmit }: StudentFormProps) {
  const [name, setName] = useState('');
  const [leergroep, setLeergroep] = useState<1 | 2 | 3>(1);
  const [photoUrl, setPhotoUrl] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [showCropper, setShowCropper] = useState(false);
  const [selectedFile, setSelectedFile] = useState<File | null>(null);

  useEffect(() => {
    if (student) {
      setName(student.name);
      setLeergroep(student.leergroep);
      setPhotoUrl(student.photo_url);
    }
  }, [student]);

  const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      setSelectedFile(file);
      setShowCropper(true);
    }
  };

  const handleCroppedImage = async (croppedFile: File) => {
    try {
      const fileExt = croppedFile.name.split('.').pop();
      const fileName = `${Date.now()}.${fileExt}`;
      
      const { data, error } = await supabase.storage
        .from('student_photos')
        .upload(fileName, croppedFile);

      if (error) throw error;

      const { data: { publicUrl } } = supabase.storage
        .from('student_photos')
        .getPublicUrl(fileName);

      setPhotoUrl(publicUrl);
      setShowCropper(false);
    } catch (err: any) {
      setError(err.message || 'Fout bij uploaden van foto');
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      if (student) {
        // Update existing student
        const { error } = await supabase
          .from('students')
          .update({
            name,
            leergroep,
            photo_url: photoUrl,
            updated_at: new Date().toISOString()
          })
          .eq('id', student.id);

        if (error) throw error;
      } else {
        // Create new student
        const { error } = await supabase
          .from('students')
          .insert([{
            name,
            leergroep,
            photo_url: photoUrl,
            cohort_id: cohortId
          }]);

        if (error) throw error;
      }

      onSubmit();
    } catch (err: any) {
      setError(err.message || 'Er is een fout opgetreden');
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div className="bg-white rounded-lg shadow-xl w-full max-w-md">
          <div className="flex justify-between items-center p-6 border-b border-gray-200">
            <h3 className="text-lg font-semibold text-gray-900">
              {student ? 'Leerling bewerken' : 'Nieuwe leerling'}
            </h3>
            <button
              onClick={onClose}
              className="text-gray-400 hover:text-gray-600"
            >
              <X className="w-6 h-6" />
            </button>
          </div>

          <form onSubmit={handleSubmit} className="p-6">
            <div className="mb-4">
              <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-2">
                Naam
              </label>
              <input
                id="name"
                type="text"
                value={name}
                onChange={(e) => setName(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Voornaam Achternaam"
                required
              />
            </div>

            <div className="mb-4">
              <label htmlFor="leergroep" className="block text-sm font-medium text-gray-700 mb-2">
                Leergroep
              </label>
              <select
                id="leergroep"
                value={leergroep}
                onChange={(e) => setLeergroep(Number(e.target.value) as 1 | 2 | 3)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value={1}>Leergroep 1</option>
                <option value={2}>Leergroep 2</option>
                <option value={3}>Leergroep 3</option>
              </select>
            </div>

            <div className="mb-4">
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Foto
              </label>
              <div className="flex items-center gap-4">
                <div className="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center overflow-hidden">
                  {photoUrl ? (
                    <img
                      src={photoUrl}
                      alt="Preview"
                      className="w-full h-full object-cover"
                    />
                  ) : (
                    <User className="w-6 h-6 text-gray-400" />
                  )}
                </div>
                <div className="flex-1">
                  <input
                    type="file"
                    accept="image/*"
                    onChange={handleFileSelect}
                    className="hidden"
                    id="photo-upload"
                  />
                  <label
                    htmlFor="photo-upload"
                    className="flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 cursor-pointer transition-colors"
                  >
                    <Upload className="w-4 h-4" />
                    Foto selecteren
                  </label>
                  <p className="text-xs text-gray-500 mt-1">
                    Foto wordt automatisch bijgesneden
                  </p>
                </div>
              </div>
            </div>

            {error && (
              <div className="mb-4 bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
                {error}
              </div>
            )}

            <div className="flex gap-3">
              <button
                type="button"
                onClick={onClose}
                className="flex-1 px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
              >
                Annuleren
              </button>
              <button
                type="submit"
                disabled={loading}
                className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-2"
              >
                {loading ? (
                  <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                ) : (
                  <>
                    <Save className="w-4 h-4" />
                    {student ? 'Bijwerken' : 'Toevoegen'}
                  </>
                )}
              </button>
            </div>
          </form>
        </div>
      </div>

      {showCropper && selectedFile && (
        <ImageCropper
          file={selectedFile}
          onCrop={handleCroppedImage}
          onClose={() => setShowCropper(false)}
        />
      )}
    </>
  );
}