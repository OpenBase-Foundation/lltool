export interface Database {
  public: {
    Tables: {
      cohorts: {
        Row: {
          id: string;
          name: string;
          owner_id: string;
          created_at: string;
          updated_at: string;
        };
        Insert: {
          id?: string;
          name: string;
          owner_id: string;
          created_at?: string;
          updated_at?: string;
        };
        Update: {
          id?: string;
          name?: string;
          owner_id?: string;
          created_at?: string;
          updated_at?: string;
        };
      };
      cohort_access: {
        Row: {
          id: string;
          cohort_id: string;
          user_id: string;
          permissions: 'view' | 'edit';
          created_at: string;
        };
        Insert: {
          id?: string;
          cohort_id: string;
          user_id: string;
          permissions: 'view' | 'edit';
          created_at?: string;
        };
        Update: {
          id?: string;
          cohort_id?: string;
          user_id?: string;
          permissions?: 'view' | 'edit';
          created_at?: string;
        };
      };
      students: {
        Row: {
          id: string;
          name: string;
          leergroep: 1 | 2 | 3;
          photo_url: string | null;
          cohort_id: string;
          created_at: string;
          updated_at: string;
        };
        Insert: {
          id?: string;
          name: string;
          leergroep: 1 | 2 | 3;
          photo_url?: string | null;
          cohort_id: string;
          created_at?: string;
          updated_at?: string;
        };
        Update: {
          id?: string;
          name?: string;
          leergroep?: 1 | 2 | 3;
          photo_url?: string | null;
          cohort_id?: string;
          created_at?: string;
          updated_at?: string;
        };
      };
    };
  };
}

export type Cohort = Database['public']['Tables']['cohorts']['Row'];
export type CohortAccess = Database['public']['Tables']['cohort_access']['Row'];
export type Student = Database['public']['Tables']['students']['Row'];