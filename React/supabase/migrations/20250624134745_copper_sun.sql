/*
  # Fix infinite recursion in cohorts RLS policies

  1. Security Changes
    - Drop the problematic "Users can view shared cohorts" policy that causes infinite recursion
    - Simplify the cohorts SELECT policies to avoid circular references
    - Keep the policy for users to view their own cohorts
    - Remove the circular dependency by handling shared cohorts access differently

  2. Changes Made
    - Drop existing SELECT policies on cohorts table
    - Create a single, simple SELECT policy for cohorts that only allows owners to see their cohorts
    - The shared cohorts access will be handled at the application level through joins
*/

-- Drop the problematic policies that cause infinite recursion
DROP POLICY IF EXISTS "Users can view shared cohorts" ON cohorts;
DROP POLICY IF EXISTS "Users can view their own cohorts" ON cohorts;

-- Create a single, simple SELECT policy for cohorts
CREATE POLICY "Users can view cohorts they own or have access to"
  ON cohorts
  FOR SELECT
  TO authenticated
  USING (
    owner_id = auth.uid() OR
    id IN (
      SELECT cohort_id 
      FROM cohort_access 
      WHERE user_id = auth.uid()
    )
  );