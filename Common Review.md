# For this project, I would recommend:
```Controller -> Service -> Repository -> PDO, with Models``` kept simple and consistent.

# Project should use supported php version and in this case add typed properties

# A good target shape is:
- thin ProjectController
- ProjectService, TaskService
- ProjectRepository, TaskRepository with interfaces
- Project, Task, NotFoundException
- DatabaseConnectionFactory or injected PDO wrapper
- Move config data to ```.env``` file

# Common for both models
- Add a type hint for constructor input to match the documented internal array structure.
- Validate the incoming payload shape before storing it in the model.
- Avoid using the generic ```$_data``` bag as the entire domain model; expose explicit task fields instead.
- Rename ```$_data``` to a clearer property name; the underscore-style naming is inconsistent with modern PHP conventions.
- Consider making the model immutable to avoid accidental state inconsistencies.
- Add explicit getters for important task fields instead of exposing only raw serialized data.
- Keep model serialization strategy consistent with Project; one model uses JsonSerializable while the other uses toJson().
- Separate domain responsibility from transport responsibility if this model is meant to be more than a DB row wrapper.
- Add PHPDoc or typed-property detail for the expected keys inside the task payload for readability and maintenance.
