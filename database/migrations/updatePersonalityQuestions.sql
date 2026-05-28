-- =====================================================
-- Update Personality Questions to Social Preference Focus
-- Migration: updatePersonalityQuestions.sql
-- Date: May 27, 2026
-- =====================================================

-- Delete existing personality questions
DELETE FROM personality_questions;

-- Insert new social preference-focused questions
INSERT INTO personality_questions (category, question_text, weight, display_order, is_active) VALUES
('social_preference', 'After a long day, how do you usually recharge?', 1.00, 1, 1),
('social_preference', 'How comfortable are you with starting conversations with new people?', 1.00, 2, 1),
('social_preference', 'What type of room environment do you prefer most?', 1.00, 3, 1),
('social_preference', 'When living with roommates, which best describes you?', 1.00, 4, 1),
('social_preference', 'How often do you join social activities or gatherings?', 1.00, 5, 1),
('social_preference', 'If your roommates invite friends over, how would you react?', 1.00, 6, 1),
('social_preference', 'Which statement describes you best?', 1.00, 7, 1),
('social_preference', 'How do you usually handle group discussions or teamwork?', 1.00, 8, 1),
('social_preference', 'What kind of roommate would make you most comfortable?', 1.00, 9, 1),
('social_preference', 'Which best describes your personality overall?', 1.00, 10, 1);

-- Note: Answer options will be handled in the model/view
-- Question 1: 1=Spend time alone quietly, 2=Talk with close friends, 3=Depends on my mood and energy, 4=I enjoy being around many people
-- Question 2: 1=Very uncomfortable, 2=Slightly uncomfortable, 3=Comfortable when needed, 4=Very comfortable
-- Question 3: 1=Quiet and peaceful, 2=Balanced and calm, 3=Social but manageable, 4=Active and lively
-- Question 4: 1=I value personal space most, 2=I interact occasionally, 3=I balance alone time and socializing, 4=I enjoy frequent interaction
-- Question 5: 1=Rarely, 2=Sometimes, 3=Often if invited, 4=Very often
-- Question 6: 1=I would rather avoid the interaction, 2=I'm okay with it occasionally, 3=It depends on my mood, 4=I would probably join them
-- Question 7: 1=I think before speaking and keep to myself, 2=I open up only to certain people, 3=I can adapt socially depending on the situation, 4=I naturally express myself openly
-- Question 8: 1=Prefer listening instead of speaking, 2=Speak only when necessary, 3=Participate comfortably, 4=Usually lead or energize the group
-- Question 9: 1=Quiet and independent, 2=Respectful and balanced, 3=Flexible and understanding, 4=Friendly and socially active
-- Question 10: 1=Mostly Introverted, 2=Slightly Introverted, 3=Balanced / Ambivert, 4=Mostly Extroverted, 5=Depends heavily on mood and environment (Omnivert)

