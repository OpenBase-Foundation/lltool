import React from "react";

interface MigrationWarningModalProps {
  onClose: () => void;
}

const MigrationWarningModal: React.FC<MigrationWarningModalProps> = ({ onClose }) => {
  return (
    <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div className="bg-white p-6 rounded shadow-lg max-w-md w-full">
        <h2 className="text-xl font-bold mb-4">Waarschuwing: Mogelijke memory leak</h2>
        <p className="mb-4">
          Door een recente migratie kan er de komende dagen een mogelijk memory leak optreden. We raden aan om alert te zijn op onverwacht gedrag. Dit bericht verdwijnt automatisch na 3 dagen.
        </p>
        <button
          className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
          onClick={onClose}
        >
          Sluiten
        </button>
      </div>
    </div>
  );
};

export default MigrationWarningModal; 