/*
  # Fix infinite recursion in cohorts RLS policy

  1. Problem
    - The current SELECT policy on cohorts table creates circular dependency
    - Policy references cohort_access table which also references cohorts table
    - This causes infinite recursion when evaluating policies

  2. Solution
    - Simplify the cohorts SELECT policy to avoid circular reference
    - Use a more direct approach that doesn't create dependency loops
    - Maintain security while breaking the recursion

  3. Changes
    - Drop existing problematic SELECT policy on cohorts
    - Create new simplified SELECT policy
    - Ensure users can still access cohorts they own or have been granted access to
*/

-- Drop the existing problematic SELECT policy
DROP POLICY IF EXISTS "Users can view cohorts they own or have access to" ON cohorts;

-- Create a simplified SELECT policy that avoids circular dependency
CREATE POLICY "Users can view their own cohorts"
  ON cohorts
  FOR SELECT
  TO authenticated
  USING (owner_id = auth.uid());

-- Create a separate policy for shared cohorts using a function to avoid recursion
CREATE OR REPLACE FUNCTION user_has_cohort_access(cohort_uuid uuid, user_uuid uuid)
RETURNS boolean
LANGUAGE sql
SECURITY DEFINER
STABLE
AS $$
  SELECT EXISTS (
    SELECT 1 
    FROM cohort_access 
    WHERE cohort_id = cohort_uuid 
    AND user_id = user_uuid
  );
$$;

-- Create policy for shared cohorts using the function
CREATE POLICY "Users can view shared cohorts"
  ON cohorts
  FOR SELECT
  TO authenticated
  USING (user_has_cohort_access(id, auth.uid()));