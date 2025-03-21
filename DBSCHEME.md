# DataBase Scheme:

for our database scheme, this is the plan:

1. Users Table
2. Profiles Table:\
    a table that stores information about the user like image or preferences.
3. Categories Table:\
    the category of a puzzle, like if it is a chess puzzle, a math puzzle, etc.
4. Communities Table:\
    a community is like an online group of users who share similar interests, like the same school, or enjoing the same categories of puzzles, etc.
5. Puzzles Table:\
    there is multiple approaches for implementing the puzzles data, depending on the type of the puzzle(text, chess, etc...).
    1. The first one is to store the puzzles data as a json column, so the encoding and decoding of the puzzle's data as json depends 
    on its category.
    2. The second approach is to create multiple tables for each type of puzzle.
    the decision to make depends on the ability of making new puzzle types.
6. Solutoins Table:\
    this table stores the choices for each puzzle, or in case there are no choices (like chess for example), we store the solution, 
    also as json, or as 3 columns for choices, and a column for the right answer.
7. Answers Table:\
    the users answers.
8. 
