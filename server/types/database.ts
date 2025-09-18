// Database type definitions for MySQL
export interface User {
  id: string;
  email: string;
  password_hash: string;
  created_at: string;
  updated_at: string;
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

// Insert types (without auto-generated fields)
export interface UserInsert {
  email: string;
  password_hash: string;
}

export interface CohortInsert {
  name: string;
  owner_id: string;
}

export interface CohortAccessInsert {
  cohort_id: string;
  user_id: string;
  permissions: 'view' | 'edit';
}

export interface StudentInsert {
  name: string;
  leergroep: 1 | 2 | 3;
  photo_url?: string | null;
  cohort_id: string;
}

// Update types (all fields optional except id)
export interface UserUpdate {
  id: string;
  email?: string;
  password_hash?: string;
}

export interface CohortUpdate {
  id: string;
  name?: string;
  owner_id?: string;
}

export interface CohortAccessUpdate {
  id: string;
  cohort_id?: string;
  user_id?: string;
  permissions?: 'view' | 'edit';
}

export interface StudentUpdate {
  id: string;
  name?: string;
  leergroep?: 1 | 2 | 3;
  photo_url?: string | null;
  cohort_id?: string;
}
