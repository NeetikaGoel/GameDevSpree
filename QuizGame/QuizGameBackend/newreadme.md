

SO NEW FLOW OF GAMECONFIG 




1. Query file: only stores SQL text
2. Mapper file: converts raw DB rows into PHP objects or arrays
3. Repository file: coordinates query + DB execution + mapper
4. DBManager: talks to MySQL
5. OrmManager: sits between repository and DBManager to make mapping reusable

So the flow is:

Repository -> Query -> DBManager/OrmManager -> Mapper -> final PHP object that will be used in the project






