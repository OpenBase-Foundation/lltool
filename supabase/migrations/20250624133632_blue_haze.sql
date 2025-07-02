/*
  # Student Management System Database Schema

  1. New Tables
    - `cohorts`
      - `id` (uuid, primary key)  
      - `name` (text)
      - `owner_id` (uuid, references auth.users)
      - `created_at` (timestamp)
      - `updated_at` (timestamp)
    
    - `cohort_access`
      - `id` (uuid, primary key)
      - `cohort_id` (uuid, references cohorts)
      - `user_id` (uuid, references auth.users)
      - `permissions` (text, 'view' or 'edit')
      - `created_at` (timestamp)
    
    - `students`
      - `id` (uuid, primary key)
      - `name` (text)
      - `leergroep` (integer, 1-3)
      - `photo_url` (text, nullable)
      - `cohort_id` (uuid, references cohorts)
      - `created_at` (timestamp)
      - `updated_at` (timestamp)

  2. Security
    - Enable RLS on all tables
    - Add policies for cohort owners and shared users
    - Secure student data access through cohort permissions

  3. Storage
    - Create student_photos bucket for photo storage
    - Set up policies for authenticated users
*/

-- Drop tables if they exist (in juiste volgorde ivm foreign keys)
DROP TABLE IF EXISTS cohort_access;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS cohorts;

-- Create cohorts table
CREATE TABLE IF NOT EXISTS cohorts (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  name text NOT NULL,
  owner_id uuid REFERENCES auth.users(id) ON DELETE CASCADE,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now()
);

-- Create cohort_access table for sharing
CREATE TABLE IF NOT EXISTS cohort_access (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  cohort_id uuid REFERENCES cohorts(id) ON DELETE CASCADE,
  user_id uuid REFERENCES auth.users(id) ON DELETE CASCADE,
  permissions text NOT NULL CHECK (permissions IN ('view', 'edit')),
  created_at timestamptz DEFAULT now(),
  UNIQUE(cohort_id, user_id)
);

-- Create students table
CREATE TABLE IF NOT EXISTS students (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  name text NOT NULL,
  leergroep integer NOT NULL CHECK (leergroep IN (1, 2, 3)),
  photo_url text,
  cohort_id uuid REFERENCES cohorts(id) ON DELETE CASCADE,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now()
);

-- Enable RLS
ALTER TABLE cohorts ENABLE ROW LEVEL SECURITY;
ALTER TABLE cohort_access ENABLE ROW LEVEL SECURITY;
ALTER TABLE students ENABLE ROW LEVEL SECURITY;

-- Cohorts policies
CREATE POLICY "Users can view their own cohorts"
  ON cohorts
  FOR SELECT
  TO authenticated
  USING (owner_id = auth.uid());

CREATE POLICY "Users can view shared cohorts"
  ON cohorts
  FOR SELECT
  TO authenticated
  USING (
    EXISTS (
      SELECT 1 FROM cohort_access 
      WHERE cohort_access.cohort_id = cohorts.id 
      AND cohort_access.user_id = auth.uid()
    )
  );

CREATE POLICY "Users can insert their own cohorts"
  ON cohorts
  FOR INSERT
  TO authenticated
  WITH CHECK (owner_id = auth.uid());

CREATE POLICY "Users can update their own cohorts"
  ON cohorts
  FOR UPDATE
  TO authenticated
  USING (owner_id = auth.uid());

CREATE POLICY "Users can delete their own cohorts"
  ON cohorts
  FOR DELETE
  TO authenticated
  USING (owner_id = auth.uid());

-- Cohort access policies
CREATE POLICY "Users can view cohort access for their cohorts"
  ON cohort_access
  FOR SELECT
  TO authenticated
  USING (
    EXISTS (
      SELECT 1 FROM cohorts 
      WHERE cohorts.id = cohort_access.cohort_id 
      AND cohorts.owner_id = auth.uid()
    )
  );

CREATE POLICY "Users can insert cohort access for their cohorts"
  ON cohort_access
  FOR INSERT
  TO authenticated
  WITH CHECK (
    EXISTS (
      SELECT 1 FROM cohorts 
      WHERE cohorts.id = cohort_access.cohort_id 
      AND cohorts.owner_id = auth.uid()
    )
  );

CREATE POLICY "Users can delete cohort access for their cohorts"
  ON cohort_access
  FOR DELETE
  TO authenticated
  USING (
    EXISTS (
      SELECT 1 FROM cohorts 
      WHERE cohorts.id = cohort_access.cohort_id 
      AND cohorts.owner_id = auth.uid()
    )
  );

-- Students policies
CREATE POLICY "Users can view students in their cohorts"
  ON students
  FOR SELECT
  TO authenticated
  USING (
    EXISTS (
      SELECT 1 FROM cohorts 
      WHERE cohorts.id = students.cohort_id 
      AND cohorts.owner_id = auth.uid()
    )
    OR
    EXISTS (
      SELECT 1 FROM cohort_access 
      WHERE cohort_access.cohort_id = students.cohort_id 
      AND cohort_access.user_id = auth.uid()
    )
  );

CREATE POLICY "Users can insert students in their cohorts"
  ON students
  FOR INSERT
  TO authenticated
  WITH CHECK (
    EXISTS (
      SELECT 1 FROM cohorts 
      WHERE cohorts.id = students.cohort_id 
      AND cohorts.owner_id = auth.uid()
    )
    OR
    EXISTS (
      SELECT 1 FROM cohort_access 
      WHERE cohort_access.cohort_id = students.cohort_id 
      AND cohort_access.user_id = auth.uid()
      AND cohort_access.permissions = 'edit'
    )
  );

CREATE POLICY "Users can update students in their cohorts"
  ON students
  FOR UPDATE
  TO authenticated
  USING (
    EXISTS (
      SELECT 1 FROM cohorts 
      WHERE cohorts.id = students.cohort_id 
      AND cohorts.owner_id = auth.uid()
    )
    OR
    EXISTS (
      SELECT 1 FROM cohort_access 
      WHERE cohort_access.cohort_id = students.cohort_id 
      AND cohort_access.user_id = auth.uid()
      AND cohort_access.permissions = 'edit'
    )
  );

CREATE POLICY "Users can delete students in their cohorts"
  ON students
  FOR DELETE
  TO authenticated
  USING (
    EXISTS (
      SELECT 1 FROM cohorts 
      WHERE cohorts.id = students.cohort_id 
      AND cohorts.owner_id = auth.uid()
    )
    OR
    EXISTS (
      SELECT 1 FROM cohort_access 
      WHERE cohort_access.cohort_id = students.cohort_id 
      AND cohort_access.user_id = auth.uid()
      AND cohort_access.permissions = 'edit'
    )
  );

-- Create storage bucket for student photos
INSERT INTO storage.buckets (id, name, public) 
VALUES ('student_photos', 'student_photos', true)
ON CONFLICT (id) DO NOTHING;

-- Storage policies
CREATE POLICY "Users can upload photos for their students"
  ON storage.objects
  FOR INSERT
  TO authenticated
  WITH CHECK (bucket_id = 'student_photos');

CREATE POLICY "Users can view student photos"
  ON storage.objects
  FOR SELECT
  TO authenticated
  USING (bucket_id = 'student_photos');

CREATE POLICY "Users can update student photos"
  ON storage.objects
  FOR UPDATE
  TO authenticated
  USING (bucket_id = 'student_photos');

CREATE POLICY "Users can delete student photos"
  ON storage.objects
  FOR DELETE
  TO authenticated
  USING (bucket_id = 'student_photos');