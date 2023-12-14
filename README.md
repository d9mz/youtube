# uTue: A 2009/2010 YouTube Experience
![UTue](Screenshot%202023-12-13%20220833.png)

## Overview
**uTue** is a meticulously crafted homage to YouTube as it was in 2009/2010. This project represents my most successful effort to date in replicating the look, feel, and functionality of YouTube during this iconic era of internet history.

## Features
- **Authentic Interface:** Recreates YouTube's 2009/2010 user interface, offering a nostalgic trip for those who experienced it first-hand and a glimpse into the past for new explorers.
- **Functional Simulation:** Beyond aesthetics, uTue emulates the operational aspects of old YouTube, providing a functional and interactive experience.
- **Accuracy:** Most important to recreating YouTube's aesthetic, was the old, skeuomorphic player and intricate channel customization.

## Technical Stack
- **Core Technologies:** Utilizes raw PHP, integrating a custom router and the Twig templating engine.
- **Database Handling:** Employs a mediocre approach to model inclusion, and this led to some performance challenges.

## Challenges and Learnings
- **Performance Issues:** The initial framework, while innovative, had limitations. Each new Model created a fresh database connection, leading to significant performance bottlenecks.
- **Lack of Migrations:** The absence of database migration practices resulted in the schema being lost to time, so it is not provided in this repository.

## Future Directions
- **Reflection and Growth:** This project serves as a valuable learning experience, highlighting both the potential and pitfalls of custom framework development.
- **Possible Revival with Laravel:** There's a possibility of revisiting and revamping this project in the future, potentially leveraging Laravel to enhance its structure and efficiency.
