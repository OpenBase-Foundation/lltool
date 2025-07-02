import React, { useState } from 'react';
import { useAuth } from './hooks/useAuth';
import { LoginForm } from './components/Auth/LoginForm';
import { Header } from './components/Layout/Header';
import { CohortList } from './components/Cohorts/CohortList';
import { StudentList } from './components/Students/StudentList';
import { Cohort } from './lib/database.types';

function App() {
  const { user, loading } = useAuth();
  const [selectedCohort, setSelectedCohort] = useState<Cohort | null>(null);

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-100 flex items-center justify-center">
        <div className="text-center">
          <div className="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
          <p className="text-gray-600">Laden...</p>
        </div>
      </div>
    );
  }

  if (!user) {
    return <LoginForm onSuccess={() => {}} />;
  }

  return (
    <div className="min-h-screen bg-gray-100">
      <Header />
      
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <div className="lg:col-span-1">
            <CohortList
              selectedCohort={selectedCohort}
              onSelectCohort={setSelectedCohort}
            />
          </div>
          
          <div className="lg:col-span-2">
            {selectedCohort ? (
              <StudentList cohort={selectedCohort} />
            ) : (
              <div className="bg-white rounded-lg shadow-sm p-12 text-center">
                <h2 className="text-xl font-semibold text-gray-900 mb-4">
                  Selecteer een cohort
                </h2>
                <p className="text-gray-600">
                  Kies een cohort uit de lijst om de leerlingen te bekijken en te beheren.
                </p>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

export default App;