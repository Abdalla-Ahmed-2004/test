# API Routes Documentation

This document describes all the available API routes for the backend, including their purpose and usage.

## Authentication Routes

- **POST** `/register`
    - Description: Register a new user.

- **POST** `/login`
    - Description: Log in an existing user.

- **POST** `/logout`
    - Description: Log out the current user. Requires authentication.

- **GET** `/me`
    - Description: Get the current authenticated user's details. Requires authentication.

## Subject Routes

- **GET** `/subjects`
    - Description: Retrieve all subjects.

- **GET** `/subjects/{subject}/teachers`
    - Description: Retrieve all teachers for a specific subject.

- **GET** `/subjects/{subject}/teachers/{teacher}/lessons`
    - Description: Retrieve all lessons for a specific teacher and subject.

- **GET** `/subjects/{subject}/teachers/{teacher}/lessons/{lesson}/content`
    - Description: Retrieve the content of a specific lesson.

- **GET** `/subjects/{subject}/teachers/{teacher}/lessons/{lesson}/videos/{video}/quizzes/{quiz}`
    - Description: Retrieve a specific quiz for a lesson video. Requires student role.

- **POST** `/subjects/{subject}/teachers/{teacher}/lessons/{lesson}/videos/{video}/quizzes/{quiz}/answers`
    - Description: Submit answers for a quiz. Requires student role.

- **GET** `/subjects/{subject}/subtopics`
    - Description: Retrieve all subtopics for a specific subject.

## Student Routes

- **GET** `/students/{student}/attempts`
    - Description: Retrieve all quiz attempts for a specific student. Requires student role.

## Quiz Routes

- **GET** `/quizzes`
    - Description: Retrieve all quizzes.

- **GET** `/quizzes/{quiz}/questions/{question}`
    - Description: Retrieve a specific question for a quiz.

- **API Resource** `/quizzes`
    - Description: Full CRUD operations for quizzes. Requires teacher role.

## Video Routes

- **API Resource** `/videos`
    - Description: Full CRUD operations for videos. Requires teacher role.

## Resource Routes

- **API Resource** `/quizzes`
  - Description: Provides full CRUD operations for quizzes. Requires teacher role.
  - **GET** `/quizzes`: Retrieve all quizzes.
  - **POST** `/quizzes`: Create a new quiz.
  - **GET** `/quizzes/{quiz}`: Retrieve a specific quiz by ID.
  - **PUT** `/quizzes/{quiz}`: Update a specific quiz by ID.
  - **DELETE** `/quizzes/{quiz}`: Delete a specific quiz by ID.

- **API Resource** `/videos`
  - Description: Provides full CRUD operations for videos. Requires teacher role.
  - **GET** `/videos`: Retrieve all videos.
  - **POST** `/videos`: Create a new video.
  - **GET** `/videos/{video}`: Retrieve a specific video by ID.
  - **PUT** `/videos/{video}`: Update a specific video by ID.
  - **DELETE** `/videos/{video}`: Delete a specific video by ID.

---

### Notes

- All routes under `auth:api` middleware require authentication.
- Replace `{parameter}` with the actual ID or value for the resource.
- Use appropriate HTTP methods (GET, POST, etc.) as specified.
