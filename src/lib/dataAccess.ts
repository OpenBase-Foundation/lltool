import { apiClient, Cohort, Student, CohortAccess } from './api';

export class DataAccess {
  // Cohort operations
  static async getCohorts(): Promise<Cohort[]> {
    const response = await apiClient.getCohorts();
    if (response.error) {
      throw new Error(response.error);
    }
    return response.data || [];
  }

  static async getCohort(id: string): Promise<Cohort> {
    const response = await apiClient.getCohort(id);
    if (response.error) {
      throw new Error(response.error);
    }
    if (!response.data) {
      throw new Error('Cohort not found');
    }
    return response.data;
  }

  static async createCohort(name: string): Promise<Cohort> {
    const response = await apiClient.createCohort(name);
    if (response.error) {
      throw new Error(response.error);
    }
    if (!response.data) {
      throw new Error('Failed to create cohort');
    }
    return response.data;
  }

  static async updateCohort(id: string, name: string): Promise<Cohort> {
    const response = await apiClient.updateCohort(id, name);
    if (response.error) {
      throw new Error(response.error);
    }
    if (!response.data) {
      throw new Error('Failed to update cohort');
    }
    return response.data;
  }

  static async deleteCohort(id: string): Promise<void> {
    const response = await apiClient.deleteCohort(id);
    if (response.error) {
      throw new Error(response.error);
    }
  }

  static async shareCohort(cohortId: string, userId: string, permissions: 'view' | 'edit'): Promise<CohortAccess> {
    const response = await apiClient.shareCohort(cohortId, userId, permissions);
    if (response.error) {
      throw new Error(response.error);
    }
    if (!response.data) {
      throw new Error('Failed to share cohort');
    }
    return response.data;
  }

  static async getCohortAccess(cohortId: string): Promise<CohortAccess[]> {
    const response = await apiClient.getCohortAccess(cohortId);
    if (response.error) {
      throw new Error(response.error);
    }
    return response.data || [];
  }

  static async removeCohortAccess(accessId: string): Promise<void> {
    const response = await apiClient.removeCohortAccess(accessId);
    if (response.error) {
      throw new Error(response.error);
    }
  }

  // Student operations
  static async getStudents(cohortId: string): Promise<Student[]> {
    const response = await apiClient.getStudents(cohortId);
    if (response.error) {
      throw new Error(response.error);
    }
    return response.data || [];
  }

  static async getStudent(id: string): Promise<Student> {
    const response = await apiClient.getStudent(id);
    if (response.error) {
      throw new Error(response.error);
    }
    if (!response.data) {
      throw new Error('Student not found');
    }
    return response.data;
  }

  static async createStudent(student: Omit<Student, 'id' | 'created_at' | 'updated_at'>): Promise<Student> {
    const response = await apiClient.createStudent(student);
    if (response.error) {
      throw new Error(response.error);
    }
    if (!response.data) {
      throw new Error('Failed to create student');
    }
    return response.data;
  }

  static async updateStudent(id: string, student: Partial<Student>): Promise<Student> {
    const response = await apiClient.updateStudent(id, student);
    if (response.error) {
      throw new Error(response.error);
    }
    if (!response.data) {
      throw new Error('Failed to update student');
    }
    return response.data;
  }

  static async deleteStudent(id: string): Promise<void> {
    const response = await apiClient.deleteStudent(id);
    if (response.error) {
      throw new Error(response.error);
    }
  }

  // File operations
  static async uploadFile(file: File): Promise<string> {
    const response = await apiClient.uploadFile(file);
    if (response.error) {
      throw new Error(response.error);
    }
    if (!response.data) {
      throw new Error('Upload failed');
    }
    return response.data.url;
  }
}
