TABLE students
  id INTEGER NOT NULL PRIMARY KEY
  name VARCHAR(30) NOT NULL

TABLE studentActivities
  studentId INTEGER NOT NULL,
  activity VARCHAR(30) NOT NULL,
  PRIMARY KEY (studentId, activity),
  FOREIGN KEY (studentId) REFERENCES students(id)


Write a query that returns the ids and names of students that take either the "Tennis" or "Football" activity.


SELECT id, name
FROM students
INNER JOIN studentActivities
ON students.id = studentActivities.studentId
WHERE activity="Tennis" OR activity="Football";