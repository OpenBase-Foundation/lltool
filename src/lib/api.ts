// API client to replace Supabase client
const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:3001/api';

export interface ApiResponse<T> {
  data?: T;
  error?: string;
}

export interface User {
  id: string;
  email: string;
}

export interface Cohort {
  id: string;
  name: string;
  owner_id: string;
  created_at: string;
  updated_at: string;
}

export interface CohortAccess {
  id: string;
  cohort_id: string;
  user_id: string;
  permissions: 'view' | 'edit';
  created_at: string;
}

export interface Student {
  id: string;
  name: string;
  leergroep: 1 | 2 | 3;
  photo_url: string | null;
  cohort_id: string;
  created_at: string;
  updated_at: string;
}

class ApiClient {
  private token: string | null = null;

  constructor() {
    // Load token from localStorage on initialization
    this.token = localStorage.getItem('auth_token');
  }

  setToken(token: string | null) {
    this.token = token;
    if (token) {
      localStorage.setItem('auth_token', token);
    } else {
      localStorage.removeItem('auth_token');
    }
  }

  private async request<T>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<ApiResponse<T>> {
    const url = `${API_BASE_URL}${endpoint}`;
    
    const headers: HeadersInit = {
      'Content-Type': 'application/json',
      ...options.headers,
    };

    if (this.token) {
      headers.Authorization = `Bearer ${this.token}`;
    }

    try {
      const response = await fetch(url, {
        ...options,
        headers,
      });

      const data = await response.json();

      if (!response.ok) {
        return { error: data.error || 'An error occurred' };
      }

      return { data };
    } catch (error) {
      return { error: 'Network error occurred' };
    }
  }

  // Auth methods
  async signUp(email: string, password: string): Promise<ApiResponse<{ user: User; token: string }>> {
    const response = await this.request<{ user: User; token: string }>('/auth/register', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    });

    if (response.data) {
      this.setToken(response.data.token);
    }

    return response;
  }

  async signIn(email: string, password: string): Promise<ApiResponse<{ user: User; token: string }>> {
    const response = await this.request<{ user: User; token: string }>('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    });

    if (response.data) {
      this.setToken(response.data.token);
    }

    return response;
  }

  async signOut(): Promise<ApiResponse<void>> {
    this.setToken(null);
    return { data: undefined };
  }

  async getCurrentUser(): Promise<ApiResponse<{ user: User }>> {
    return this.request<{ user: User }>('/auth/verify');
  }

  // Cohort methods
  async getCohorts(): Promise<ApiResponse<Cohort[]>> {
    return this.request<Cohort[]>('/cohorts');
  }

  async getCohort(id: string): Promise<ApiResponse<Cohort>> {
    return this.request<Cohort>(`/cohorts/${id}`);
  }

  async createCohort(name: string): Promise<ApiResponse<Cohort>> {
    return this.request<Cohort>('/cohorts', {
      method: 'POST',
      body: JSON.stringify({ name }),
    });
  }

  async updateCohort(id: string, name: string): Promise<ApiResponse<Cohort>> {
    return this.request<Cohort>(`/cohorts/${id}`, {
      method: 'PUT',
      body: JSON.stringify({ name }),
    });
  }

  async deleteCohort(id: string): Promise<ApiResponse<void>> {
    return this.request<void>(`/cohorts/${id}`, {
      method: 'DELETE',
    });
  }

  async shareCohort(cohortId: string, userId: string, permissions: 'view' | 'edit'): Promise<ApiResponse<CohortAccess>> {
    return this.request<CohortAccess>(`/cohorts/${cohortId}/share`, {
      method: 'POST',
      body: JSON.stringify({ user_id: userId, permissions }),
    });
  }

  async getCohortAccess(cohortId: string): Promise<ApiResponse<CohortAccess[]>> {
    return this.request<CohortAccess[]>(`/cohorts/${cohortId}/access`);
  }

  async removeCohortAccess(accessId: string): Promise<ApiResponse<void>> {
    return this.request<void>(`/cohorts/access/${accessId}`, {
      method: 'DELETE',
    });
  }

  // Student methods
  async getStudents(cohortId: string): Promise<ApiResponse<Student[]>> {
    return this.request<Student[]>(`/students/cohort/${cohortId}`);
  }

  async getStudent(id: string): Promise<ApiResponse<Student>> {
    return this.request<Student>(`/students/${id}`);
  }

  async createStudent(student: Omit<Student, 'id' | 'created_at' | 'updated_at'>): Promise<ApiResponse<Student>> {
    return this.request<Student>('/students', {
      method: 'POST',
      body: JSON.stringify(student),
    });
  }

  async updateStudent(id: string, student: Partial<Student>): Promise<ApiResponse<Student>> {
    return this.request<Student>(`/students/${id}`, {
      method: 'PUT',
      body: JSON.stringify(student),
    });
  }

  async deleteStudent(id: string): Promise<ApiResponse<void>> {
    return this.request<void>(`/students/${id}`, {
      method: 'DELETE',
    });
  }

  // File upload method
  async uploadFile(file: File): Promise<ApiResponse<{ url: string }>> {
    const formData = new FormData();
    formData.append('photo', file);

    const url = `${API_BASE_URL}/upload`;
    
    const headers: HeadersInit = {};
    if (this.token) {
      headers.Authorization = `Bearer ${this.token}`;
    }

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers,
        body: formData,
      });

      const data = await response.json();

      if (!response.ok) {
        return { error: data.error || 'Upload failed' };
      }

      return { data };
    } catch (error) {
      return { error: 'Upload error occurred' };
    }
  }
}

export const apiClient = new ApiClient();
