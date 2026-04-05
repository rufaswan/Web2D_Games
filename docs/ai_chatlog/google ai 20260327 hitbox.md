In gaming and technology, hitbox most commonly refers to the invisible shapes used for collision detection in software or a specific type of high-performance arcade controller. [1, 2]

1. In-Game Collision Detection [1]

A hitbox is an invisible geometric shape (like a box, sphere, or cylinder) that defines the area where a character or object can interact with other elements in the game world. [3, 4, 5]

* Purpose: Developers use these simplified shapes because calculating collisions against complex, high-polygon character models is too taxing for processors.
* Hitbox vs. Hurtbox: In many competitive games (especially fighting games), these are distinct:
* Hitbox: The area of an attack (like a fist or a sword) that can cause damage.
   * Hurtbox: The area of a character's body that can receive damage.
* Gameplay Impact: A hitbox that is larger than the visible character model may feel "unfair" to players, while a smaller one might make a character feel "dodgy" or harder to hit. [3, 5, 6, 7, 8, 9, 10, 11]

2. Hit Box Arcade Controllers

Hit Box is also the brand name of the first commercially available leverless controller. These devices replace the traditional joystick with four buttons for movement. [9, 12, 13, 14, 15]

* Layout: Typically uses three buttons for Left, Down, and Right, with a larger thumb button at the bottom for "Up" (jumping).
* Advantages: Leverless controllers offer extreme input precision and speed because they eliminate the physical travel time required to move a joystick.
* SOCD: They use "Simultaneous Opposing Cardinal Directions" (SOCD) cleaning to handle cases where two opposite buttons (like Left and Right) are pressed at once, which can unlock faster shortcuts for certain moves. [13, 14, 15, 16, 17]

3. Other Meanings

* Streaming: Hitbox.tv was a live-streaming platform (similar to Twitch) that eventually merged into Smashcast.
* Analytics: Hitbox was an early web analytics tool used for monitoring website traffic, later acquired by Adobe.
* Marketing: HiTBOX Social is a social media strategy agency focused on precise content growth for brands. [18, 19, 20, 21]

Are you looking for hitbox visualizations for a specific game, or are you interested in buying a leverless controller?

[1] [https://www.larksuite.com](https://www.larksuite.com/en_us/topics/gaming-glossary/hitbox)
[2] [https://developer.valvesoftware.com](https://developer.valvesoftware.com/wiki/Hitbox#:~:text=A%20hitbox%20is%20an%20invisible%20box%20%28or,calculations%20on%2C%20so%20hitboxes%20are%20used%20instead.)
[3] [https://www.g2a.com](https://www.g2a.com/news/glossary/hitboxes-in-games-a-simple-guide/)
[4] [https://catnessgames.com](https://catnessgames.com/wikigame/hitbox/#:~:text=Hitbox.%20Rectangle%2C%20cube%2C%20circle%2C%20or%20sphere%20%28depending,an%20enemy%20or%20an%20object%20takes%20damage.)
[5] [https://www.ssbwiki.com](https://www.ssbwiki.com/Hitbox)
[6] [https://developer.valvesoftware.com](https://developer.valvesoftware.com/wiki/Hitbox)
[7] [https://www.reddit.com](https://www.reddit.com/r/explainlikeimfive/comments/x0onso/eli5_what_is_a_hit_box_in_regards_to_a_video_game/)
[8] [https://www.reddit.com](https://www.reddit.com/r/explainlikeimfive/comments/x0onso/eli5_what_is_a_hit_box_in_regards_to_a_video_game/)
[9] [https://glossary.infil.net](https://glossary.infil.net/?t=Hitbox)
[10] [https://streetfighter.fandom.com](https://streetfighter.fandom.com/wiki/Hitbox)
[11] [https://www.reddit.com](https://www.reddit.com/r/CrazyHand/comments/fiamwk/explain_the_concept_of_hitboxeshurtboxes_to_me_in/)
[12] [https://www.reddit.com](https://www.reddit.com/r/fightsticks/comments/zyc4w6/what_is_a_hitboxstyle_fightstick_and_why_does_it/)
[13] [https://glossary.infil.net](https://glossary.infil.net/?t=Hitbox%20%28controller%29)
[14] [https://www.youtube.com](https://www.youtube.com/shorts/O5eBnuk-WYU)
[15] [https://www.hitboxarcade.com](https://www.hitboxarcade.com/blogs/hit-box/hit-box-info-page)
[16] https://www.hitboxarcade.com
[17] [https://www.youtube.com](https://www.youtube.com/watch?v=VG6n234U79Q&t=96)
[18] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/Hitbox_%28web_analytics%29)
[19] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/Hitbox_%28service%29)
[20] [https://www.facebook.com](https://www.facebook.com/people/Hitbox-Social/61569137940606/)
[21] [https://www.instagram.com](https://www.instagram.com/hitbox_social/)

# ---

In game development, the term "hitbox" is often a catch-all for various types of invisible collision shapes, each serving a unique purpose. These are broadly categorized into functional types and then defined by specific data attributes. [1, 2]

Common Types of Hitboxes

While specific names vary by game engine, most developers use these three fundamental types: [2, 3]

* Attackbox (Offensive Hitbox): The area of an attack (like a sword swing or a projectile) that deals damage when it overlaps with an opponent.
* Hurtbox (Damageable Area): The area on a character's body that can receive damage. If an attackbox touches a hurtbox, a "hit" is registered.
* Collision Box (Pushbox): Determines the physical space an object occupies. It prevents characters from walking through walls or overlapping each other. [1, 3, 4, 5, 6]

Specialized fighting game hitboxes include:

* Grabbox: Specialized hitboxes that ignore standard defensive states to trigger a throw.
* Reflect/Absorb Box: Defensive areas that can redirect or nullify projectiles.
* Windbox: A hitbox that doesn't deal damage but applies a "pushing" force to the opponent.
* Intangible/Invincible Box: States where a character's hurtboxes are temporarily deactivated or modified so they cannot be hit. [1, 7, 8]

Hitbox Attributes

An "attribute" (or property) is a piece of data attached to a hitbox that tells the game engine how to handle a collision once it happens. Common attributes include: [8]

* Damage: The amount of health removed from the target on impact.
* Priority: A value (often an ID number) that determines which attack "wins" if two hitboxes collide on the same frame.
* Angle: The specific direction (0-360 degrees) the target is sent flying after being hit.
* Knockback (Base & Growth): Determines the initial force of the hit and how much that force increases as the target's health/percentage gets lower.
* Active Frames: The specific timeframe during an animation when the hitbox is "on" and capable of detecting collisions.
* Element/Effect: Adds visual or mechanical traits like fire, electricity, or "poison" damage.
* Hitlag Multiplier: Determines how many frames the game "freezes" for both players to emphasize the impact of the hit. [1, 7, 8, 9, 10, 11]

Are you interested in how these are implemented in a specific game engine like Unity or Unreal, or are you looking for the hitbox data for a specific game?

[1] [https://www.ssbwiki.com](https://www.ssbwiki.com/Hitbox)
[2] [https://www.g2a.com](https://www.g2a.com/news/glossary/hitboxes-in-games-a-simple-guide/)
[3] [https://www.g2a.com](https://www.g2a.com/news/glossary/hitboxes-in-games-a-simple-guide/)
[4] [https://gaming.stackexchange.com](https://gaming.stackexchange.com/questions/182350/what-do-hitbox-and-hurtbox-mean)
[5] [https://www.gamedeveloper.com](https://www.gamedeveloper.com/design/hitboxes-and-hurtboxes-in-unity)
[6] [https://www.youtube.com](https://www.youtube.com/watch?v=xcWavcszac4&t=37)
[7] [https://www.reddit.com](https://www.reddit.com/r/Games/comments/5oinp6/what_are_hitboxes_fighting_games_5_minute/)
[8] [https://supersmashbros.fandom.com](https://supersmashbros.fandom.com/wiki/Hitbox)
[9] [https://www.reddit.com](https://www.reddit.com/r/gamedev/comments/501hg9/a_wonderful_explanation_of_how_hitboxes_work/)
[10] [https://www.g2a.com](https://www.g2a.com/news/glossary/hitboxes-in-games-a-simple-guide/)
[11] [https://www.ssbwiki.com](https://www.ssbwiki.com/Hitbox)

# ---

Fighting games and beat-’em-ups share a foundation of collision logic but differ significantly in how they handle spatial precision and interaction depth. [1]

Common Hitbox Data (Shared)

Both genres rely on a core set of boxes to manage combat:

* Hurtboxes: Define where a character can be hit.
* Hitboxes (Attackboxes): Define the area of an attack. A "hit" occurs when these two boxes overlap.
* Frame Data: Both use "Startup," "Active," and "Recovery" frames to determine move timing and safety.
* Collision/Space Boxes: Invisible "pushboxes" that prevent characters from walking through each other. [2, 3, 4, 5, 6, 7, 8]

Exclusive to Each Genre

The primary difference is that fighting games prioritize 1v1 precision, while beat-’em-ups focus on managing crowds in 3D space. [9, 10, 11]

| Feature [3, 4, 7, 8, 9, 12, 13, 14, 15, 16, 17] | Fighting Games (Exclusive/Priority) | Beat-’em-Up Games (Exclusive/Priority) |
|---|---|---|
| Z-Axis Logic | Rarely used (usually strictly 2D/side-on). | Vital. Boxes must account for depth (up/down on the screen) so you can miss an enemy by being "too high" or "too low" in the lane. |
| Precision | High. Characters often have separate hurtboxes for head, torso, and legs to allow for specific "high/low" interactions. | Low. Usually use one large "body box" for simplicity. Over-precision can feel "unfair" when fighting many enemies. |
| Proximity Boxes | Common. Triggers special animations (like a "close" vs. "far" punch) or forces a character to block when an opponent is near. | Rare. Most moves have a fixed animation regardless of proximity. |
| Throw Boxes | Highly Detailed. Specific boxes (often blue) that determine if a grapple can connect, usually with their own priority rules. | Simplified. Often integrated into standard attack boxes or triggered by simple proximity/state checks. |
| Pushbox Physics | Strict. Characters almost always push each other. | Loose. Enemies may overlap or "stack" on top of each other to allow the player to hit multiple targets at once. |

Clarification on "Gender": In game development, hitboxes do not have "gender" in a biological sense. However, character archetypes (often gender-coded in classic games) influence hitbox data. "Heavy" characters (often male) typically have larger hurtboxes (easier to hit) and higher knockback values, while "Agile" characters (often female) feature smaller, thinner hurtboxes and faster startup frames. [7, 18]

Would you like to see a breakdown of how frame data specifically differs between these genres?

[1] [https://www.chronocrash.com](https://www.chronocrash.com/forum/threads/hit-boxes-in-beat-em-ups.7099/)
[2] [https://www.youtube.com](https://www.youtube.com/watch?v=WIZrnYk-GkI&t=98)
[3] [https://www.reddit.com](https://www.reddit.com/r/Games/comments/5oinp6/what_are_hitboxes_fighting_games_5_minute/)
[4] [https://www.reddit.com](https://www.reddit.com/r/Games/comments/5oinp6/what_are_hitboxes_fighting_games_5_minute/)
[5] [https://www.youtube.com](https://www.youtube.com/shorts/S2LE9OAINSw)
[6] [https://www.reddit.com](https://www.reddit.com/r/Fighters/comments/esgwo2/what_exactly_is_frame_data_and_how_do_i_read_it/)
[7] [https://www.reddit.com](https://www.reddit.com/r/gamedev/comments/1lww48/2d_fighting_games_question_about_hitboxes/)
[8] [https://www.dustloop.com](https://www.dustloop.com/w/Hitboxes)
[9] [https://www.chronocrash.com](https://www.chronocrash.com/forum/threads/hit-boxes-in-beat-em-ups.7099/)
[10] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/List_of_video_game_genres)
[11] [https://www.reddit.com](https://www.reddit.com/r/truegaming/comments/7oc4bd/fighting_games_need_to_incorporate_beatemup/)
[12] [https://www.dummies.com](https://www.dummies.com/article/home-auto-hobbies/sports-recreation/esports/esports-the-basics-of-the-fighting-game-genre-270300/)
[13] [https://www.patreon.com](https://www.patreon.com/posts/beat-em-ups-dev-134611716)
[14] [https://www.chronocrash.com](https://www.chronocrash.com/forum/threads/hit-boxes-in-beat-em-ups.7099/)
[15] [https://www.youtube.com](https://www.youtube.com/watch?v=b4Kc1p6Iat8&t=78)
[16] [https://www.reddit.com](https://www.reddit.com/r/CharacterActionGames/comments/100jc8f/what_would_you_say_are_the_main_differences/)
[17] [https://siegarettes.medium.com](https://siegarettes.medium.com/what-makes-a-good-beat-em-up-ee31b557337b)
[18] [https://www.youtube.com](https://www.youtube.com/shorts/KlDyG3-iqc0)

# ---

In fighting games, double buffering does not exist to make characters "less responsive." In fact, developers and competitive players generally avoid any technique that adds unnecessary delay, as responsiveness is critical for high-level play. [1, 2, 3, 4, 5]

1. Does it render "late"?

Double buffering itself adds a one-frame delay. [6]

* How it works: The GPU draws an image in a "back buffer" while the "front buffer" is being displayed on your monitor. They only swap once the next frame is fully ready.
* The Result: You are always seeing the previous frame that was completed, rather than the one currently being calculated. This ensures a clean, tear-free image, but at the cost of about 16.67ms of lag at 60 FPS. [4, 6, 7, 8, 9]

2. Is this "Intentional" Delay?

No, it is a technical trade-off for visual stability (preventing screen tearing), not a design choice for "feel". [10, 11]

* Competitive Priority: Most fighting game players disable V-Sync and buffering entirely to get "zero-frame" or "sub-frame" latency, even if it causes screen tearing.
* Netcode Exceptions: The only time delay is added intentionally is in Delay-Based Netcode. In this scenario, the game pauses your local input for a few frames (e.g., 3-frame delay) so it has time to receive your opponent's data over the internet, ensuring both characters move at the same time. [6, 12, 13, 14, 15]

3. Buffering vs. Responsiveness

* Double Buffering: Adds 1 frame of delay. It is the "faster" of the sync options.
* Triple Buffering: Adds 2 frames of delay. It is avoided in fighting games because it makes the game feel "muddy" or sluggish.
* Input Buffers: Don't confuse frame buffering with an input buffer. An input buffer is a gameplay feature that "remembers" your button press for a few frames so your move still comes out even if you're slightly early. This actually makes the game feel more responsive and forgiving. [4, 16, 17, 18, 19]

Would you like to know how to minimize input lag in your specific game settings?

[1] [https://www.youtube.com](https://www.youtube.com/watch?v=-wjxmM6LPr0&t=148)
[2] [https://www.youtube.com](https://www.youtube.com/watch?v=3SWV9aIQ-XY)
[3] [https://www.reddit.com](https://www.reddit.com/r/Fighters/comments/18gtnef/could_someone_explain_why_mostif_not_all_fighting/)
[4] [https://forums.tomshardware.com](https://forums.tomshardware.com/threads/vsync-double-or-triple-buffering.2711418/)
[5] [https://www.reddit.com](https://www.reddit.com/r/Fighters/comments/1aq7m35/input_delay_in_fighting_games/#:~:text=Yes%2C%20fighting%20games%20and%20by%20extension%20all,slow%20down%20similar%20to%20delay%20based%20netcode.)
[6] [https://news.ycombinator.com](https://news.ycombinator.com/item?id=14800222)
[7] [https://gamedev.stackexchange.com](https://gamedev.stackexchange.com/questions/82318/what-problem-does-double-or-triple-buffering-solve-in-modern-games)
[8] [https://www.reddit.com](https://www.reddit.com/r/Fighters/comments/jvwj9v/does_rising_the_frame_rate_gonna_change_the_frame/)
[9] [https://forums.guru3d.com](https://forums.guru3d.com/threads/why-not-always-have-triple-buffering-on.376645/)
[10] [https://www.reddit.com](https://www.reddit.com/r/pcgaming/comments/7eiaq4/double_or_triple_buffered_vsync_for_lowest_input/)
[11] [https://www.intel.com](https://www.intel.com/content/www/us/en/support/articles/000006930/graphics.html)
[12] [https://steamcommunity.com](https://steamcommunity.com/app/1364780/discussions/0/4626979978390549499/?ctp=1)
[13] [https://www.reddit.com](https://www.reddit.com/r/pcgaming/comments/7eiaq4/double_or_triple_buffered_vsync_for_lowest_input/)
[14] [https://arstechnica.com](https://arstechnica.com/gaming/2019/10/explaining-how-fighting-games-use-delay-based-and-rollback-netcode/)
[15] [https://www.reddit.com](https://www.reddit.com/r/gamedesign/comments/crnp96/why_do_fighting_games_create_additional_input_lag/)
[16] [https://forums.guru3d.com](https://forums.guru3d.com/threads/with-the-low-lag-trick-does-triple-buffering-or-double-buffer-adaptive-v-sync-have-lower-input-lag.435027/)
[17] [https://www.youtube.com](https://www.youtube.com/watch?v=2AuPBhEdJHk&t=77)
[18] [https://forums.anandtech.com](https://forums.anandtech.com/threads/triple-buffering-adds-or-reduces-input-lag.2380763/)
[19] [https://www.reddit.com](https://www.reddit.com/r/gamedesign/comments/crnp96/why_do_fighting_games_create_additional_input_lag/)

# ---

When a player says a fighting game is "too responsive," they are almost never complaining about low input lag. Instead, they are usually using "responsive" as a shorthand for a lack of weight, commitment, or deliberate pacing.

In fighting game terms, this usually refers to three specific mechanical "problems":

1. Lack of "Startup" or "Wind-up"

In a "heavy" or "grounded" fighter (like Street Fighter or Tekken), moves have a clear beginning, middle, and end. If a game is "too responsive," characters might transition from standing to attacking instantly (1–3 frames).

* The Complaint: The game feels "twitchy" or "jittery" because there is no visual buildup to an action, making it impossible to react to an opponent's moves.

2. Infinite "Cancelability"

A game feels "too responsive" if you can cancel any move into any other move at any time.

* The Commitment Issue: In traditional fighters, once you press a heavy button, you are "committed" to that animation. If a game lets you cancel a massive swing into a block or a dash instantly, it removes the risk of attacking.
* The Complaint: The game feels like "button mashing" because there are no consequences for making a mistake.

3. "Floaty" Movement

If a character changes direction in mid-air or stops a full sprint instantly without any "friction" or "inertia" frames, the game can feel "too responsive."

* The Complaint: The characters don't feel like they have mass. They feel like cursors on a screen rather than physical combatants. This is common in "Air Dashers" or "Anime Fighters" (Guilty Gear, Dragon Ball FighterZ), which some players find overwhelming compared to "stiff" or "heavy" games.

4. Over-Aggressive Input Buffering

As mentioned before, an input buffer remembers your button press. If the buffer is too large (e.g., 10+ frames), the game might execute a move you pressed a half-second ago even if you've changed your mind.

* The Complaint: "The game is doing things I didn't want it to do!" or "It's too sensitive."

In short: They aren't complaining that the hardware is too fast; they are complaining that the animation and physics lack weight and commitment.

Are you trying to tune the feel of a character's movement in a project you're working on?

# ---

In game development, the shift from Vec2 to Vec3 typically signals one of two things: a move into 3D space or the addition of a time/depth attribute.

Here is how Vec3(x, y, z) is commonly used for hitbox data:

1. 3D Spatial Volume (Most Common)

In a 3D game (like Tekken, SoulCalibur, or a modern Beat-'em-up), a Vec2 only defines a flat plane. Adding a z coordinate defines a Volume.

* X: Width (Left/Right)
* Y: Height (Up/Down)
* Z: Depth (In/Out of the screen)
* Usage: This ensures that an attack only hits if the characters are standing in the same "lane" or depth-plane.

2. Cylinder or Capsule Dimensions

Many 3D games use Capsules (a cylinder with spheres on top) for hitboxes because they are computationally "cheap." In this case, the Vec3 defines the shape's geometry:

* X: Radius
* Y: Height
* Z: (Often unused or used for a secondary offset/tilt)

3. Position + Radius (Sphere)

If the hitbox is a perfect sphere (common for projectiles like a Hadouken), the Vec3 might represent:

* X, Y: The center coordinates on the screen.
* Z: The Radius (the size of the circle).

4. 2D + Depth Layer (2.5D Games)

In "2.5D" Beat-'em-ups (like Streets of Rage 4 or River City Girls), the game looks 3D but uses 2D physics. Here, Vec3 often represents:

* X, Y: The 2D position on the floor.
* Z: The Altitude (how high the character has jumped off the ground).
* Data Logic: An attack hitbox only connects if its Z (height) matches the victim's Z (altitude) range.

5. Hitbox "Growth" (The Time Attribute)

In some advanced engines, a Vec3 for a single hitbox attribute might represent interpolation:

* X: Starting size.
* Y: Ending size.
* Z: Duration (how many frames it takes to grow from X to Y).

Are you defining these hitboxes in a 3D engine (like Unreal/Unity) or a 2D engine with layers?

# ---

In a 2D game, a hitbox with a Vec2 shape (for size) but Vec3 data (for properties) usually indicates that the developer is packing combat physics into a single vector to save space and simplify calculations.

The Vec3 is not usually used for a 3D position; instead, each component (X, Y, and Z) represents a different attribute of the hit. [1]

Common 2D Combat Interpretations of Vec31. Knockback and Force (Vec3(Strength, Angle, Scaling))

This is the most common use in fighting games and beat-'em-ups. [2]

* X (Strength/Base): The initial power of the hit. It tells the game how many pixels to shove the opponent backward.
* Y (Angle): The launch direction in degrees (e.g., 45 for a launcher, 0 for a horizontal push, or 270 for a "spike" that slams them into the ground).
* Z (Scaling/Growth): How much the knockback increases based on the victim's current damage. Higher Z means the move gets much deadlier as the opponent gets weaker. [2, 3]

2. Visual Layering (Vec3(X_Offset, Y_Offset, Z_Layer))

If the Vec3 refers to the hitbox's placement rather than its effect:

* X & Y: The relative position of the box compared to the character's center point.
* Z (Sorting Order): Even in 2D games, engines like Unity or Godot use the Z-axis to determine which object is "in front" of the other. This prevents your attack from hitting an enemy standing "behind" a background wall. [4, 5, 6]

3. Stat Packing (Vec3(Damage, Hitstun, Hitstop))

Developers often use a Vec3 to pass "The Big Three" stats of a move to the damage-dealing function in one go: [7]

* X (Damage): How much health to subtract.
* Y (Hitstun): How many frames the opponent is "frozen" and unable to move or counter-attack.
* Z (Hitstop): The "shudder" effect where both the attacker and the victim freeze for a few frames to make the hit feel heavy and impactful. [2, 8]

4. The "Beat-'em-up Lane" (Vec3(X, Y, Depth_Width))

In side-scrolling beat-'em-ups (like Streets of Rage), characters walk up and down "lanes."

* X & Y: Position on the 2D plane.
* Z: The thickness of the hitbox into the background. This determines if your punch hits an enemy who is walking in a slightly different "lane" than you. [9, 10]

Are you currently scripting the damage logic for these boxes, or are you setting up the values in a character editor?

[1] [https://www.youtube.com](https://www.youtube.com/watch?v=sYf4bSj9j2w&t=372)
[2] [https://www.youtube.com](https://www.youtube.com/watch?v=gD_Gq3LTENA&t=405)
[3] [https://www.reddit.com](https://www.reddit.com/r/Unity3D/comments/1jccnii/knockback_angles_for_rigidbody_collisions/)
[4] [https://www.reddit.com](https://www.reddit.com/r/Unity2D/comments/1kkdkuk/im_making_a_hitbox_editor_for_a_2d_fighting_game/)
[5] [https://stackoverflow.com](https://stackoverflow.com/questions/37104152/vector2d-or-vector3d-in-2d-games)
[6] [https://discussions.unity.com](https://discussions.unity.com/t/why-does-2d-vector-use-vector3-instead-of-vector2/822115)
[7] [https://www.reddit.com](https://www.reddit.com/r/Unity3D/comments/7nrm85/animating_an_attack_hit_boxes_weapon_trails/)
[8] [https://game.capcom.com](https://game.capcom.com/cfn/sfv/column/131545?lang=en)
[9] [https://www.chronocrash.com](https://www.chronocrash.com/forum/threads/hit-boxes-in-beat-em-ups.7099/)
[10] [https://www.youtube.com](https://www.youtube.com/watch?v=cdwIUloOJKo&t=37)

# ---

In modern 2D game architecture, each individual Vec2 hitbox typically has its own unique Vec3 attribute pairing.

This is because hitboxes are rarely just "shapes"—they are data packages that tell the game what to do when that specific part of an animation connects. [1, 2, 3]

The 1:1 Relationship

Each frame of an animation (like a punch or a kick) contains a list of hitboxes. For every hitbox in that list: [4]

* The Vec2 defines the Physical Shape (e.g., width and height).
* The Vec3 defines the Combat Logic (e.g., Damage, Knockback, and Hitstun). [2, 5]

If you used one Vec3 for multiple hitboxes, every part of the move would feel identical. Developers pair them 1:1 so they can "fine-tune" the impact:

* The "Sweet Spot": A small Vec2 at the tip of a sword might have a high-damage Vec3 (e.g., [20, 45, 10]).
* The "Sour Spot": A larger Vec2 near the hilt might have a low-damage Vec3 (e.g., [5, 10, 5]). [6, 7]

Exceptions (The 1:Many Setup)

The only time you would see one Vec3 applied to multiple Vec2 shapes is for Global Move Properties.

* The Logic: If an entire move (like a spin-kick) is designed to deal exactly the same damage and knockback regardless of where it hits, the developer might point all three hitboxes to a single "Attribute Profile" to save memory and make balance changes easier.

Why use a Vec3 instead of just three separate numbers?

   1. Optimization: Computers can process a single "Vector" object faster than three individual floating-point numbers.
   2. Implicit Conversion: Many engines (like Unity) treat all positions as 3D by default; using a Vec3 avoids unnecessary data conversion ("casting") behind the scenes. [5, 8, 9, 10]

Are you trying to assign different damage values to specific parts of a single attack?

[1] [https://discussions.unity.com](https://discussions.unity.com/t/2d-hitboxes-hurtboxes-based-on-frame/621525?page=2)
[2] [https://www.youtube.com](https://www.youtube.com/watch?v=99Y9C26fBMk&t=377)
[3] [https://www.youtube.com](https://www.youtube.com/shorts/_oswYqg0E7E)
[4] [https://www.reddit.com](https://www.reddit.com/r/gamedev/comments/vjxmpe/3d_hitboxes_and_authoring_tools/)
[5] [https://www.youtube.com](https://www.youtube.com/watch?v=sYf4bSj9j2w&t=372)
[6] [https://discussions.unity.com](https://discussions.unity.com/t/how-can-i-do-frame-by-frame-hitbox-control-for-a-2d-fighting-game-character/59211)
[7] [https://www.reddit.com](https://www.reddit.com/r/godot/comments/1fx9qe2/how_to_create_multiple_hitboxes_that_can_be/)
[8] [https://stackoverflow.com](https://stackoverflow.com/questions/37104152/vector2d-or-vector3d-in-2d-games)
[9] [https://www.youtube.com](https://www.youtube.com/watch?v=_z2Y715-3wM&t=162)
[10] [https://discussions.unity.com](https://discussions.unity.com/t/should-i-use-vector3d-in-a-2d-game/194980)

# ---

To test and identify stacked hitboxes in a 2D fighting game, you need to use a Debug Overlay (often called "Hitbox Viewer"). Developers and competitive players use these to visualize the invisible data.

Since hitboxes often overlap, they are traditionally identified by a universal Color Code and Layering Logic.

1. Identify by Color (The Standard)

Almost every fighting game uses this specific color language to differentiate stacked boxes:

* Red (Hitbox/Attackbox): The active part of the move that deals damage.
* Green/Blue (Hurtbox): The vulnerable part of your character.
* Yellow/White (Collision/Pushbox): The physical "solid" center that prevents players from walking through each other.
* Cyan/Purple (Grabbox): A special box that only interacts with "throwable" states.

2. Identify by "Depth" (Layering)

When boxes are stacked, they follow a Priority Hierarchy. You can test which is which by observing how they interact with an opponent:

* Pushboxes are Always Active: If you walk toward an opponent and your character stops before touching their sprite, you’ve found the Yellow/Collision box.
* Hurtboxes are Internal: Usually, the Green box is slightly smaller than the character's art.
* Hitboxes are External: During an attack, the Red box will suddenly appear, often extending beyond the Green hurtbox.

3. The "Frame-by-Frame" Test

To truly identify them, you must use Training Mode with "Frame Advance" enabled:

   1. Freeze the game at the start of an attack.
   2. Advance one frame at a time.
   3. Watch for the color change. If a Red box appears over a Green box, the Red box is your Vec2 shape and the Vec3 data (damage/knockback) is now "active."
   4. Overlap Test: If a Red box and a Green box overlap, the game registers a hit. If a Red box and a Red box overlap, it’s a "clash" or "trade."

4. Technical Identification (The "ID" Attribute)

If you are looking at the actual code or a modding tool:

* Hitbox ID: Stacked boxes usually have an ID (e.g., Index 0, Index 1).
* The "Priority" Attribute: In your Vec3 data, one of those values (often the Z or a hidden 4th value) dictates which box takes precedence. If two hitboxes on the same character touch the opponent, the one with the higher priority attribute is the only one that applies its damage.

Are you using a specific engine's debug tool (like Unity’s Gizmos or Unreal’s Collision View) to see these?

# ---

If you cannot use a Hitbox Viewer or Training Mode, you must rely on "blind testing" methods to reverse-engineer where hitboxes are located and what their Vec3 attributes are.

1. The "Pixel-by-Pixel" Movement Test

To find the edge of a hitbox without seeing it, use a second controller (or a friend) and perform the following:

* Identify the Hurtbox (Green/Blue): Walk your character slowly toward the opponent’s standing attack. The exact pixel where you take damage is the edge of your hurtbox meeting their hitbox.
* Identify the Collision Box (Yellow): Walk toward the opponent without attacking. The point where you "stick" and can no longer move forward is the edge of the collision boxes.
* Identify the Hitbox (Red): Stand just outside of what looks like the move's range. Gradually move one pixel closer and strike. The furthest point at which the move still connects is the maximum reach of that Vec2 shape. [1, 2, 3]

2. Identify Vec3 Attributes by Observation

Since you can't see the data values, you have to "feel" the Vec3 components based on the result of a hit:

* X (Strength): Look at how far the opponent is pushed back on a hit. A "heavy" punch will have a higher X-value than a "light" jab.
* Y (Angle): Watch the trajectory. If they go straight up, Y is roughly 90°. If they stay on the ground, Y is 0°. If they are "spiked" downward, Y is 270°.
* Z (Growth/Scaling): Test the move when the opponent has 0% damage vs. 90% damage. If the move knocks them significantly further at high damage, the Z-attribute is high.

3. Testing "Stacked" Boxes

When multiple hitboxes are stacked on one frame (like a "Sweet Spot" on a sword), use the Health Bar as your readout:

   1. Find the overlap: Hit the opponent with the tip of the weapon, then the middle, then the hilt.
   2. Compare Damage: If the tip deals 100 damage but the hilt deals 60, you have identified two different Vec2 shapes with different Vec3 data.
   3. Check Priority: If you stand at a distance where both boxes would technically touch the opponent, the damage value that actually appears tells you which Vec3 has the higher priority.

4. External Documentation

If testing is too slow, look for "Frame Data" or "Hitbox Maps" on community wikis like [Dustloop](https://www.dustloop.com/w/Hitboxes) or DreamCancel. These sites often feature screenshots from the game's internal files where the hitboxes were previously extracted by modders.

Would you like to know how to manually calculate the pixels for these distances to get exact numbers?

[1] [https://www.reddit.com](https://www.reddit.com/r/Games/comments/5oinp6/what_are_hitboxes_fighting_games_5_minute/)
[2] [https://www.dustloop.com](https://www.dustloop.com/w/Hitboxes)
[3] [https://www.youtube.com](https://www.youtube.com/watch?v=zWUphi6qMnI&t=47)

# ---

In games where the ground has its own hitbox, the environment ceases to be a static background and becomes a dynamic participant in the combat. You typically "worry" about ground hitboxes in three types of games:

1. Platform Fighters (e.g., [Super Smash Bros.](https://www.google.com/search?kgmid=/m/05cwrp&q=what+type+of+game+you+have+to+worry+about+the+ground+itself+has+a+hitbox), [MultiVersus](https://www.google.com/search?kgmid=/g/11pd3m1dr6&q=what+type+of+game+you+have+to+worry+about+the+ground+itself+has+a+hitbox))

These games are the most common where "the floor is a hitbox."

* Stage Hazards: Floors can disappear, tilt, or transform into offensive hitboxes (like lava or electricity) that deal damage upon contact.
* Tech Chasing: When you are knocked down, your character's hurtbox interacts with the ground's collision box. If you don't "tech" (hit a button as you land), you remain vulnerable to "Off the Ground" (OTG) attacks. [1, 2, 3, 4, 5]

2. Interactive 3D Fighters (e.g., [Dead or Alive](https://www.google.com/search?kgmid=/m/03c_t7&q=what+type+of+game+you+have+to+worry+about+the+ground+itself+has+a+hitbox), [Tekken](https://www.google.com/search?kgmid=/hkb/-877416454&q=what+type+of+game+you+have+to+worry+about+the+ground+itself+has+a+hitbox))

These games use "Environmental Hitboxes" to expand combos or punish positioning. [6]

* Dangerous Ground: In the [Dead or Alive](https://www.google.com/search?kgmid=/m/03c_t7&q=what+type+of+game+you+have+to+worry+about+the+ground+itself+has+a+hitbox) series, "Danger Zones" on the floor act as explosive hitboxes that trigger when a character is slammed into them, launching them for further juggles.
* Floor Breaks: In [Tekken](https://www.google.com/search?kgmid=/m/01gqy7&q=what+type+of+game+you+have+to+worry+about+the+ground+itself+has+a+hitbox), certain floor hitboxes can be "broken" by high-impact moves, transitioning the fight to a new level below. [1, 3, 6]

3. Belt-Scrolling Beat-'Em-Ups (e.g., [Streets of Rage](https://www.google.com/search?kgmid=/hkb/-1681999706&q=what+type+of+game+you+have+to+worry+about+the+ground+itself+has+a+hitbox), [Double Dragon](https://www.google.com/search?kgmid=/m/081013&q=what+type+of+game+you+have+to+worry+about+the+ground+itself+has+a+hitbox)) [6]

Because these games use a Z-axis (depth), the ground logic is crucial. [6, 7]

* Environmental Hazards: You often have to worry about pits, landmines, or "cracking" floors that have their own trigger hitboxes. If your character's shadow (the 2D representation of your floor position) overlaps these hitboxes, you take damage or fall.
* Lanes: The ground is essentially a series of hitboxes that check if you and an enemy are in the same "lane" to allow a hit to connect. [6, 7, 8]

4. Competitive Shooters with Destruction (e.g., [Battlefield](https://www.google.com/search?kgmid=/m/03f_kc&q=what+type+of+game+you+have+to+worry+about+the+ground+itself+has+a+hitbox), [Rainbow Six Siege](https://www.google.com/search?kgmid=/m/010vvkbz&q=what+type+of+game+you+have+to+worry+about+the+ground+itself+has+a+hitbox))

While not a "fighting" game, these are the extreme example where the ground is a destructible hitbox. [6]

* Deformable Terrain: In the [Battlefield](https://www.google.com/search?kgmid=/m/03f_kc&q=what+type+of+game+you+have+to+worry+about+the+ground+itself+has+a+hitbox) series, powerful weapons can "change the hitbox of large objects" or the landscape itself, blowing holes in the floor that change where you can stand or take cover. [9]

Would you like to know how OTG (Off The Ground) logic specifically uses these floor hitboxes to extend combos?

[1] [https://www.reddit.com](https://www.reddit.com/r/Fighters/comments/184j9mf/what_do_you_think_of_fighting_games_that_have/)
[2] [https://www.youtube.com](https://www.youtube.com/watch?v=jvSEY4kYEXw&t=26)
[3] [https://www.youtube.com](https://www.youtube.com/watch?v=nXImMd6BjCw#:~:text=Stages%20are%20a%20super%20integral%20concept%20to,many%20elements%20can%20make%20a%20stage%20memorable.)
[4] [https://www.reddit.com](https://www.reddit.com/r/Fighters/comments/oqsoky/fighting_games_where_you_can_hit_the_opponent_on/)
[5] [https://gaming.stackexchange.com](https://gaming.stackexchange.com/questions/182350/what-do-hitbox-and-hurtbox-mean)
[6] [https://www.reddit.com](https://www.reddit.com/r/Games/comments/5oinp6/what_are_hitboxes_fighting_games_5_minute/)
[7] [https://www.androidpolice.com](https://www.androidpolice.com/teenage-mutant-ninja-turtles-shredders-revenge-out-now-android/)
[8] [https://www.youtube.com](https://www.youtube.com/watch?v=xaDgGfbO61k&t=8)
[9] [https://levelup.gitconnected.com](https://levelup.gitconnected.com/the-theory-behind-first-person-hitboxes-d593ecc6de9)

# ---

In modern game development, yes, tilemap data is absolutely considered a form of hitbox. Specifically, engines like [Godot](https://docs.godotengine.org/en/stable/classes/class_tilemap.html) and [GDevelop](https://wiki.gdevelop.io/gdevelop5/objects/simple_tilemap/) allow you to assign "Physics Layers" or collision shapes directly to individual tiles within a tileset. [1, 2]

However, there is a technical distinction in how that data is handled compared to a character's "Vec2/Vec3" hitbox:

1. Static vs. Dynamic Hitboxes

* Character Hitboxes: These are "Dynamic." They move every frame and change shape based on animation data (like a punch extending).
* Tilemap Hitboxes: These are "Static." They are usually tied to a fixed grid. The game doesn't "calculate" their movement; it just checks if a dynamic object (the player) has overlapped with a specific tile ID that is marked as "Solid". [3, 4, 5]

2. The "Logic Grid"

In tilemaps, the data is often separated into two parts:

* Visual Grid: The actual artwork you see (grass, stone, water).
* Logic Grid: An invisible layer of data that tells the engine, "This tile is a wall," "This tile is a slope," or "This tile is lava (a hazard hitbox)". [4, 6]

3. Optimization (The Big Difference)

Tilemap hitboxes are significantly more efficient than standard hitboxes. Instead of checking every object in the game against every other object (which is slow), the engine uses a lookup table: [3]

   1. It takes the player's X, Y position.
   2. It divides those by the tile size (e.g., 32 pixels).
   3. It checks only that one specific tile index in the array to see if it has a collision attribute. [3, 7]

Summary of Tilemap Hitbox Types

| Type [1, 8, 9, 10, 11] | Purpose | Behavior |
|---|---|---|
| Solid/Wall | Standard platforming. | Stops movement entirely; usually a simple box. |
| Slope | Angular movement. | Uses a Collision Polygon to allow smooth upward walking. |
| Hazard | Spikes, lava, pits. | Acts as a "Hurtbox" that triggers a damage state on overlap. |
| Trigger | Doors, checkpoints. | A non-solid hitbox that fires an "event" when touched. |

Are you trying to create destructible terrain where tiles disappear when they are hit, or are you just setting up basic floor collisions?

[1] [https://wiki.gdevelop.io](https://wiki.gdevelop.io/gdevelop5/objects/simple_tilemap/)
[2] [https://stackoverflow.com](https://stackoverflow.com/questions/78573416/godot-4-how-can-i-have-a-tile-in-a-tilemap-with-a-different-clickable-area)
[3] [https://forum.gamemaker.io](https://forum.gamemaker.io/index.php?threads/what-is-the-performance-difference-between-collision-objects-vs-tilemaps.14276/)
[4] [https://developer.mozilla.org](https://developer.mozilla.org/en-US/docs/Games/Techniques/Tilemaps)
[5] [https://www.youtube.com](https://www.youtube.com/watch?v=DSfO7HPKyyw&t=281)
[6] [https://developer.mozilla.org](https://developer.mozilla.org/en-US/docs/Games/Techniques/Tilemaps#:~:text=The%20logic%20grid.%20Since%20tilemaps%20are%20an,in%20Tetris%20or%20Bejeweled%29%2C%20path%2Dfinding%20algorithms%2C%20etc.)
[7] [https://www.reddit.com](https://www.reddit.com/r/gamedev/comments/dicvn/whats_the_most_efficient_way_to_test_for/)
[8] [https://www.youtube.com](https://www.youtube.com/watch?v=XxL4_a2Ci1s)
[9] [https://www.reddit.com](https://www.reddit.com/r/godot/comments/17tmpcb/tileset_spike_hitboxes_dont_work_when_on_top_of/#:~:text=tilemap%20%28environment%20related%29%20if%20collidedObject.get_collider%28%29.is_class%28%22TileMap%22%29:%20pass%20var,=%20tiles.local_to_map%28tile_position%29%20%23%20Single%20tile%20data%20var)
[10] [https://www.youtube.com](https://www.youtube.com/watch?v=u-02qALsDmg&t=144)
[11] [https://forum.godotengine.org](https://forum.godotengine.org/t/tilemap-interaction-like-on-body-entered/69054)

# ---

In game development, a grabbox is a specialized hitbox that triggers a state change in both the attacker and the victim. Unlike a standard hitbox that just deals damage and knockback, a grabbox forces the two objects to synchronize their positions and animations. [1, 2, 3]

1. Collision Detection

The attacker generates a grabbox (often colored blue or purple in debug modes). When this box overlaps with the opponent's hurtbox, the game checks for "grab validity" (e.g., if the opponent is in a throwable state and not currently blocking a strike). [1, 3, 4, 5]

2. State Machine Transition

Once a grab is confirmed, both characters exit their current states (like "Walking" or "Idle") and enter new, synchronized states: [1, 3, 6]

* Attacker: Enters a "Grabbing" state.
* Victim: Enters a "Grabbed" state, which is a specialized version of hitstun where they lose all control over their movement. [1, 3, 5]

3. Parent-Child Relationship (Moving Together)

To move around with the enemy, the game "parents" the victim to the attacker: [7, 8]

* Attachment: The victim's position is locked to a specific socket or offset on the attacker’s model (like the character's hand or shoulder).
* Synchronization: As the attacker moves or walks, the victim’s coordinates are updated every frame to maintain that relative distance. [1, 3, 7, 9, 10]

4. The Throw Execution

When the player inputs a direction or the grab animation reaches a specific frame, the "Throw" is triggered: [1, 2]

* Detachment: The parent-child link is broken.
* Force Application: The attacker’s script applies a high velocity or knockback vector to the victim in a specific direction.
* Final Transition: The victim enters a "Knockdown" or "Tumble" state, and the attacker returns to a "Neutral" state. [1, 9, 11, 12]

Summary of Grabbox Data

| Action [1, 3, 4, 7, 8, 9] | Technical Mechanism |
|---|---|
| Detection | Grabbox overlaps Hurtbox. |
| Locking | State machine transition to "Grabbed". |
| Carrying | Set Victim's transform.parent to Attacker's hand/socket. |
| Throwing | Apply Force/Velocity and clear the Parent link. |

Would you like to know how to code a grab breakout timer so the enemy can escape if the player holds them too long?

[1] [https://www.reddit.com](https://www.reddit.com/r/gamemaker/comments/bslhuv/grabbingthrowing_enemy_animation/)
[2] [https://glossary.infil.net](https://glossary.infil.net/?t=Grab)
[3] [https://www.patreon.com](https://www.patreon.com/posts/beat-em-ups-dev-134611716)
[4] [https://www.reddit.com](https://www.reddit.com/r/Games/comments/5oinp6/what_are_hitboxes_fighting_games_5_minute/)
[5] [https://www.reddit.com](https://www.reddit.com/r/howdidtheycodeit/comments/14t8z0g/grab_enemy_in_beat_em_up_games/)
[6] [https://www.reddit.com](https://www.reddit.com/r/gamedev/comments/e1k47o/using_state_machines_for_fighting_game_inputs_but/)
[7] [https://discussions.unity.com](https://discussions.unity.com/t/need-help-with-coding-fighting-game-throws-grabs/253286)
[8] [https://discussions.unity.com](https://discussions.unity.com/t/released-complete-physics-platformer-kit/516353?page=5)
[9] [https://www.youtube.com](https://www.youtube.com/watch?v=c67hJO7kvVU)
[10] [https://discussions.unity.com](https://discussions.unity.com/t/creating-grab-throw-mechanic-in-2d-platformer/543623)
[11] [https://www.youtube.com](https://www.youtube.com/watch?v=WYbaLap5t0k&t=423)
[12] [https://discussions.unity.com](https://discussions.unity.com/t/player-knockback-pushback/24226#:~:text=Player%20Knockback%20/%20PushBack%20get%20the%20direction,movement%20speed%2C%20and%20apply%20gravity%20each%20frame.)

# ---

In a fighting game, the attacker checks the victim’s state automatically through the game engine, but the player controls the outcome (like throwing forward or backward) through their own inputs during the grab's startup.

1. How the Attacker "Checks" the State

The game engine performs a logic check the moment the grabbox overlaps with the opponent's hurtbox. This is not something the player manually does, but rather a "gatekeeper" function in the code:

* Is the victim throwable? The engine checks if the victim is currently in Hitstun or Blockstun. In most games (like Street Fighter), characters are immune to grabs during these states to prevent "infinite" loops.
* Is the victim grounded? Standard grabboxes often only check for grounded hurtboxes. If the victim is in a "Jumping" state, the check fails, and the grab whiff (misses).
* Priority Check: If both players try to grab at the exact same time, the engine checks a "Priority" attribute to decide who wins, or triggers a "Throw Tech" if they are equal. [1, 2, 3, 4]

2. How the Player "Controls" the Victim

While the victim is in the Grabbed state, they lose all control. The attacker’s player then dictates the next state transition using directional inputs: [1]

* Input-Based Branching:
* Forward Throw: If the attacker holds Forward + Grab, the state machine branches to a "Forward Throw" animation.
   * Back Throw: If the attacker holds Back + Grab, the engine teleports the victim to the opposite side and plays the "Back Throw" animation.
* The "Pummel" State: In games like Super Smash Bros., the attacker can hold the victim in a "Grabbed" state and press a button to deal minor damage (pummeling) before choosing a final throw direction.
* Positional Locking (Parenting): During the control phase, the victim’s Transform/Position is literally "parented" to a bone on the attacker's 3D model (like the Hand or Shoulder). This ensures that if the attacker moves or spins, the victim follows perfectly. [1, 5, 6]

3. Throwing the Enemy Away

The transition from "Grabbed" to "Thrown" involves the attacker's script applying new Vec3 attributes to the victim:

   1. Unparenting: The victim is no longer locked to the attacker's hand.
   2. Launching: The attacker sends a command to the victim's physics component to apply a specific Velocity and Trajectory (e.g., Up and Away).
   3. State Change: The victim transitions from "Grabbed" to a "Knockdown" or "Tumble" state where they regain their own physics but cannot move until they land. [1, 5, 7, 8]


[1] [https://glossary.infil.net](https://glossary.infil.net/?t=Grab)
[2] [https://game.capcom.com](https://game.capcom.com/cfn/sfv/column/131427?lang=en)
[3] [https://www.reddit.com](https://www.reddit.com/r/StreetFighter/comments/16rnuho/new_to_fighting_games_help_me_understand_grabs/)
[4] [https://tropedia.fandom.com](https://tropedia.fandom.com/wiki/Fighting_Game/Analysis)
[5] [https://www.youtube.com](https://www.youtube.com/watch?v=tagbyHCmHA0&t=13)
[6] [https://discussions.unity.com](https://discussions.unity.com/t/need-help-with-coding-fighting-game-throws-grabs/253286)
[7] [https://www.youtube.com](https://www.youtube.com/watch?v=D9dh6fLcRoQ&t=394)
[8] [https://www.reddit.com](https://www.reddit.com/r/gamemaker/comments/18ohm39/how_do_you_program_grab_attacks/)

# ---

In 2D fighting game programming, gatekeeper functions are the logical "checks" that must return true before a state transition (like a hit, a grab, or a special move) is allowed to occur. These functions prevent illegal actions and maintain the competitive balance of the game.

1. Grab/Throw Gatekeepers

These are the most complex gatekeepers because they manage transitions between two different character state machines.

* Is_Throwable(): Checks if the victim is in a state that can be grabbed. It returns false if the victim is currently in Hitstun or Blockstun.
* Is_Grounded(): Many standard grabs only function if both players are on the floor. It returns false if the victim's "Altitude" (Z-axis/Y-axis) is above a certain threshold.
* Is_Invulnerable(): Checks if the victim has "Throw Invulnerability" frames active, common during a "Wake-up" or specific special moves.
* Check_Priority(): If both players trigger a grab on the same frame, this function determines who "wins" the interaction or if a "Throw Tech" (break) occurs. [1, 2]

2. Hit Detection Gatekeepers

These manage the interaction between the Vec2 hitbox shape and the Vec3 attribute data.

* Has_Intersected(): The most basic geometric check. It determines if a red Attackbox polygon has physically overlapped with a green Hurtbox polygon.
* Is_Hit_Valid(): A logic check that prevents "double-hitting" on a single frame. It ensures a single active hitbox ID can only apply its Vec3 damage to a specific victim once per animation cycle.
* Check_Guard_State(): If an intersection is detected, this function checks the victim's input. If they are holding "Back," it diverts the logic from a "Hit" to a "Block," applying Blockstun instead of Hitstun. [3, 4, 5, 6]

3. State & Movement Gatekeepers

These control what the player is allowed to do based on their current situation.

* Can_Act(): Returns false if the character is currently in a "Recovery" frame or a "Stun" state, preventing them from inputting new moves.
* Is_Touching_Wall(): Checks the Collision/Pushbox against the Tilemap data. If true, it stops the character's horizontal velocity.
* Can_Cancel(): In combo-heavy games, this checks if the current animation frame allows for a "special cancel." It looks for a specific "Cancel Window" attribute in the move's data. [6, 7, 8, 9, 10]

4. Special Move Gatekeepers

* Has_Resources(): Checks if the player has enough "Meter" or "Energy" to perform a Super or EX move.
* Is_Input_Complete(): Scans the Input Buffer to see if the recent sequence of directional presses matches a pre-defined pattern (e.g., Quarter-Circle Forward). [11, 12]

Do you need the pseudocode for a specific gatekeeper, like Is_Throwable() or Check_Guard_State()?

[1] [https://www.reddit.com](https://www.reddit.com/r/StreetFighter/comments/16rnuho/new_to_fighting_games_help_me_understand_grabs/)
[2] [https://www.reddit.com](https://www.reddit.com/r/StreetFighter/comments/15uvesx/how_tf_do_you_deal_with_grabs/)
[3] [https://www.youtube.com](https://www.youtube.com/watch?v=zWUphi6qMnI&t=47)
[4] [https://www.reddit.com](https://www.reddit.com/r/Games/comments/5oinp6/what_are_hitboxes_fighting_games_5_minute/)
[5] [https://www.zhihu.com](https://www.zhihu.com/en/answer/306503415)
[6] [https://andrea-jens.medium.com](https://andrea-jens.medium.com/i-wanna-make-a-fighting-game-a-practical-guide-for-beginners-part-1-2021-update-955a4672eea5)
[7] [https://game.capcom.com](https://game.capcom.com/cfn/sfv/column/131427?lang=en)
[8] [https://www.youtube.com](https://www.youtube.com/shorts/yQ7rEx07Amg)
[9] [https://www.youtube.com](https://www.youtube.com/watch?v=aTbw71EpamY&t=186)
[10] [https://www.youtube.com](https://www.youtube.com/watch?v=KQSYuvBpNF8&t=10)
[11] [https://www.youtube.com](https://www.youtube.com/watch?v=m_kIfp1sI60&t=337)
[12] [https://www.youtube.com](https://www.youtube.com/watch?v=JM44qrFY9UY&t=178)

# ---

In game development, these are often called Standard States. While both genres share a foundation, a Beat-’em-up requires extra states to handle 3D movement (the Z-axis) and environmental interaction.

1. The "Standard" Mandatory States (Shared)

Every character, whether in Street Fighter or Streets of Rage, must have these:

* Neutral/Idle: The default "waiting" state. No inputs are being pressed.
* Move (Walk/Dash): Updates the character's X (and Z) coordinates.
* Startup: The "wind-up" frames of an attack. No hitbox is active yet.
* Active: The frames where the Vec2 hitbox is "on" and checking for a victim's hurtbox.
* Recovery: The "cooldown" frames after an attack where the player cannot move or block.
* Hitstun: The state triggered when a Vec3 damage attribute is received. The player is frozen and vulnerable.
* Knockdown: Triggered by high-knockback Vec3 data. The character is horizontal on the floor and usually has "invincibility" frames.

------------------------------
2. Genre-Specific Mandatory States

This is where the logic diverges based on the "Gatekeeper" functions and camera perspective.

Fighting Game "Exclusives"

* Crouch: A mandatory state for "High/Low" mixups. It shrinks the hurtbox height.
* Blockstun: A specialized version of hitstun where the player took "reduced" damage but is still frozen.
* Pre-Jump (Jumpsquat): 3–5 mandatory frames before leaving the ground. This is a critical "gatekeeper" that prevents players from escaping pressure instantly.
* Wake-up: The specific state of getting up from the ground. This is where "Invulnerability" gatekeepers are most active.

Beat-’em-Up "Exclusives"

* Z-Axis Navigation: Instead of "Crouch," these games require a state that handles moving "up" and "down" the screen (into the background/foreground).
* Grab/Hold: Since grabbing is a core mechanic for crowd control, "Holding an Enemy" is a mandatory state where the player can still walk around (unlike a Fighting Game where a grab is usually a fixed animation).
* Thrown (Victim): A state where the character's physics are completely controlled by an external force (the attacker) until they hit a wall or the floor.
* Object Interaction: A state for picking up, holding, and swinging weapons (pipes, bats, etc.) found on the Tilemap.

------------------------------
3. State Comparison Table

| State Type | 2D Fighting Game | Beat-'Em-Up |
|---|---|---|
| Movement | Strictly X-axis (Left/Right) | X and Z-axis (360° Floor movement) |
| Defense | Hold Back to Block (Active State) | Manual Dodge/Jump (Movement State) |
| Hurtbox | Changes shape (Crouching/Standing) | Mostly Static (Simple Cylinder/Box) |
| Death | Round ends immediately | Character flashes/disappears; Lives decrease |

The "Golden Rule" of States

A character can only be in one state at a time. The Gatekeeper functions we discussed earlier are the "bridge" between these states. For example: Is_Grounded() must be true for the Move state to transition into the Crouch state.

Do you want to see a State Transition Map showing how a player moves from Idle to Hitstun and then to Knockdown?

# ---

No, they are not the same, though they often work together. In a high-performance 2D fighting game, you typically manage them as two separate layers: the State Machine (Logic) and the Animation Controller (Visuals). [1]

1. The Core Difference: Intent vs. Display

* State Transition Map (Logic): This is the "brain." It determines what a character is legally allowed to do.
* Goal: Precision. It checks Gatekeeper functions (like Is_Grounded()) to decide if you can move from Idle to Jump.
   * Speed: Usually instantaneous. When you press a button, the logic state changes in one frame so the hitbox data can activate immediately.
* Animation Transition Map (Visuals): This is the "skin." It handles how the character looks while moving between those logic states.
* Goal: Fluidity. It manages blending and crossfading so the transition from "Running" to "Jumping" doesn't look like a robotic "cut".
   * Speed: Often takes multiple frames. It might play a 3-frame "Jumpsquat" animation before the visual character actually leaves the ground. [2, 3, 4, 5, 6, 7, 8]

2. Why they must be separate in Fighting Games

If you used the same map for both, your game would feel "muddy" or "unresponsive". [9, 10]

* Logical States are Absolute: A character is either "invincible" or "vulnerable." There is no "50% invincible" middle ground.
* Animation States are Fluid: An animation can be "50% through a transition." If your logic waited for the animation to finish blending before changing the character's state, your inputs would feel like they have a massive delay. [9, 11]

3. Comparison of Transitions

| Feature [4, 5, 6, 7, 11] | State Transition (Logic) | Animation Transition (Visuals) |
|---|---|---|
| Primary Data | Vec3 Attributes (Damage, Stun) | Sprites/Keyframes |
| Timing | Instant (Frame 0) | Over Time (Multiple Frames) |
| Interruption | Can be "forced" by external hits | Usually follows "Exit Time" or Blending |
| Result | Triggers Hitboxes/Hurtboxes | Triggers Visual Effects/Smears |

4. How they "Communicate"

The most common setup is for the State Machine to drive the Animation Controller. [12]

   1. Input: Player presses "Punch."
   2. State Logic: The State Machine immediately enters the ATTACK_STARTUP state.
   3. Command: The State Machine sends a message: "Play the Punch_Anim clip now".
   4. Visuals: The Animation Controller interrupts whatever it was doing to show the punch, even if it has to skip a few frames of a "Walk" animation to stay responsive. [2, 7, 13]

Are you using a specific tool like Unity's Animator or Unreal's State Machines, or are you writing a custom system in code?

[1] [https://dev.epicgames.com](https://dev.epicgames.com/documentation/en-us/unreal-engine/state-machines-in-unreal-engine)
[2] [https://forums.unrealengine.com](https://forums.unrealengine.com/t/animation-state-machine-is-obvious-but-what-about-the-game-logic-behind-it/81793)
[3] [https://gamedev.net](https://gamedev.net/forums/topic/713998-when-is-separating-visuals-from-state-and-logic-not-optimal/)
[4] [https://www.gamedev.net](https://www.gamedev.net/forums/topic/684913-animation-state-machine-transitions-implementation/5325337/)
[5] [https://www.youtube.com](https://www.youtube.com/watch?v=WYCjmVhiLaM&t=9)
[6] [https://docs.unity3d.com](https://docs.unity3d.com/6000.3/Documentation/Manual/class-Transition.html)
[7] [https://discussions.unity.com](https://discussions.unity.com/t/animator-play-vs-using-the-state-machine/704631)
[8] [https://andrea-jens.medium.com](https://andrea-jens.medium.com/i-wanna-make-a-fighting-game-a-practical-guide-for-beginners-part-3-2021-update-12a04e48717a)
[9] [https://discussions.unity.com](https://discussions.unity.com/t/am-i-the-only-one-using-the-animator-state-machine-for-more-than-animations/661959)
[10] [https://www.reddit.com](https://www.reddit.com/r/gamedev/comments/86jlm7/state_machine_controller_setup_for_2d_fighting/)
[11] [https://discussions.unity.com](https://discussions.unity.com/t/am-i-the-only-one-using-the-animator-state-machine-for-more-than-animations/661959)
[12] [https://stackoverflow.com](https://stackoverflow.com/questions/680695/animation-and-logic)
[13] [https://discussions.unity.com](https://discussions.unity.com/t/state-machine-and-animator-together-do-i-need-to-execute-logic-on-animator-too/843901)

# ---

In modern game development—especially for fighting games—you can technically let the animation control the state, but it is generally considered a risky practice that can lead to "input lag" or "clunky" gameplay.

However, many engines (like Unity and Unreal) facilitate this through Animation Events or Notifies.

1. How it works (Animation-Driven Logic)

In this setup, the animation acts as the "master." The code waits for the animation to reach a specific frame before changing a logical value.

* Frame 1-5: Animation plays "Wind-up." The code sees a "Notify" and sets the isInvulnerable attribute to true.
* Frame 6: Animation reaches the "Extension." It triggers a function to spawn the Vec2 Hitbox.
* Frame 12: Animation ends. It sends a "State Change" command to return the player to Idle.

2. The Danger: "The Animation Trap"

If your player state is strictly controlled by the animation, you run into the Responsiveness Problem:

* Variable Frame Rates: If the game lags and skips a frame of animation, it might skip the "Notify" that turns off a hitbox, leaving your character's attack active forever (a "perma-hitbox" bug).
* Input Delay: If the player presses "Block," but the state machine is waiting for an "Animation Transition" to finish its 0.1-second blend, the player will get hit because the Gatekeeper (Is_Blocking) hasn't updated yet.

3. The Better Way: "Code-Driven, Animation-Synced"

Professional fighting games usually flip the relationship. The State Machine dictates the timing, and the animation follows:

   1. Logic First: The code says "This attack is exactly 12 frames long."
   2. Data Sync: The code looks at a table (Data Asset) to see that on Frame 6, it must activate the Vec3 damage data.
   3. Visual Sync: The animation is simply told to play at a speed that matches those 12 frames.

4. When SHOULD you use Animation-controlled states?

There are two specific cases where it is actually better:

* Grab/Throw Sequences: Since the attacker and victim must be perfectly synchronized, using "Animation Markers" to move their bones and release the "Grabbed" state ensures they don't de-sync visually.
* Beat-’em-ups (Casual): In non-competitive games, using animation events is much faster to develop. It allows artists to "tune" the gameplay by simply moving keyframes around without touching the code.

Summary

* Can you? Yes.
* Should you for a Fighting Game? No (it feels "heavy" and "unresponsive").
* Should you for a Beat-’em-up? Yes (it's easier for complex interactions like picking up items).

Are you trying to sync a specific sound effect or visual particle to a hitbox frame?

# ---

In modern game development, sound effects do not usually have a "transition map" in the same way animations do, but they are managed by a highly sophisticated Audio State Machine often referred to as Audio Middleware (like [Wwise](https://sfxengine.com/blog/how-game-sound-effects-are-made) or FMOD). [1]

While an animation map focuses on blending visual frames, the audio system focuses on Dynamic Mixing and Parameter-based Logic.

1. The "Sound Event" vs. Animation State

Instead of transitioning from "Sound A" to "Sound B," sound designers create Events.

* The Logic: When the Player State changes to RUNNING, the game sends a single command to the audio system: "Play_Footsteps."
* The Transition: The audio system doesn't just play a file; it enters a specialized state that constantly checks variables like Surface_Type (Grass/Metal) and Movement_Speed. It then "blends" the audio by crossfading between different loops or layering sounds. [2, 3]

2. Audio "RTPCs" (Real-Time Parameter Controls)

This is the "map" for sound. It maps game data to audio changes:

* Pitch/Speed Mapping: If your character’s movement speed increases, the audio system uses a curve to increase the pitch and volume of the engine or wind noise.
* Distance Attenuation: A built-in "transition" that lowers volume and applies low-pass filters (muffling) based on the distance between the Hitbox of the source and the Audio Listener (usually the player's camera). [4, 5]

3. Fighting Game Specific: The "Hit" Layering

In fighting games and beat-'em-ups, sound is often more "responsive" than visuals. [6]

* Impact Layers: A single "Hit" sound is often a combination of three simultaneous layers:
1. Transient: The initial "crack" or "snap."
   2. Body: The "thud" or "crunch" (the Vec3 damage weight).
   3. Tail: The "echo" or "reverb" that tells you how large the room is.
* Stun Transitions: If a character is hit while already in HITSTUN, the audio system might trigger a "Counter-Hit" sound, which has a higher priority and overrides the current audio state to provide instant feedback to the player. [3]

4. Summary: How they Differ

| Feature [3, 5, 6] | Animation Transition Map | Audio Middleware "Map" |
|---|---|---|
| Primary Goal | Visual Blending (Frames) | Dynamic Mixing (Frequencies/Layers) |
| Trigger | State Changes / Exit Time | Events / RT Parameters |
| Logic | "Transition from Run to Jump" | "Blend Footsteps based on Surface" |
| Feedback | Visual Timing | Immediate Reaction (saves 3-4 frames of reaction time) |

Are you trying to sync specific sound cues to your active hitbox frames, or are you looking at dynamic music transitions that change when a player’s health is low?

[1] [https://www.asoundeffect.com](https://www.asoundeffect.com/gameaudioexplained/#:~:text=THE%20FOUR%20FIELDS%20OF%20GAME%20AUDIO.%20Broadly,bespoke%20systems%20directly%20within%20the%20game%27s%20engine.)
[2] [https://blog.prosoundeffects.com](https://blog.prosoundeffects.com/game-audio-sound-design-workflow-tutorial)
[3] [https://sfxengine.com](https://sfxengine.com/blog/how-game-sound-effects-are-made)
[4] [https://www.youtube.com](https://www.youtube.com/watch?v=gCys23dcCRU&t=3)
[5] [https://insider.dbsinstitute.ac.uk](https://insider.dbsinstitute.ac.uk/how-sound-effects-for-video-games-are-made)
[6] [https://www.reddit.com](https://www.reddit.com/r/gamedev/comments/czmy3z/we_made_a_video_showing_how_to_we_make_fighting/)

# ---

In a simple prototype, yes, you just play sfx.wav. But in a professional fighting game or beat-’em-up, playing a raw file every time makes the game feel repetitive, robotic, and "cheap."

To make the sound feel as dynamic as the hitboxes, developers use several parameters to "juice" the audio:

1. Random Pitch & Volume (The "Variety" Parameter)

If you play the exact same punch.wav five times in a row, the human ear notices the "machine gun effect."

* The Parameter: Every time the hitbox connects, the engine applies a Random Range (e.g., +/- 10% pitch).
* The Result: Each punch sounds slightly deeper or sharper, making it feel like a real, physical struggle rather than a recording.

2. Hit-Stop & Time Stretching (The "Weight" Parameter)

Remember the Vec3 data we discussed? If a hit has high Hit-stop (freezing the game for a few frames), the sound needs to match.

* The Parameter: Time Scale. The audio engine can slow down the playback speed of the "impact" sound to match the visual freeze.
* The Result: The "crunch" feels heavier because the sound is literally "stretched" alongside the animation.

3. Panning & Attenuation (The "Spatial" Parameter)

In a 2D game, "Space" still matters.

* X-Position Panning: If the hitbox connects on the far right of the screen, the sound should play louder in the right speaker.
* Attenuation: In a beat-’em-up, if you are fighting enemies at the top of the screen (further back in the "lane"), the sound might be slightly quieter or have more reverb (echo) than a fight in the foreground.

4. Layering based on Vec3 Attributes

Instead of one .wav file, a "Hit" is often a Container of three files played at once:

* Layer 1 (The Snap): A high-frequency "crack" for the initial contact.
* Layer 2 (The Thud): A low-frequency "boom" that scales with the Damage attribute in your Vec3 data.
* Layer 3 (The Material): A specific sound for what was hit (e.g., a "clink" for armor, a "squish" for flesh, or a "thud" for a wooden crate).

5. Priority & Voice Limiting

If 10 enemies are hit by a single explosion in a beat-’em-up, you don't want 10 explosion.wav files playing at full volume—it will blow out the speakers (clipping).

* The Parameter: Virtual Voices. The engine limits the number of similar sounds and prioritizes the "loudest" or "most important" one (usually the player's hit).

Summary: Why you need more than just a .wav

| Feature | Why it's better than a raw .wav |
|---|---|
| Pitch Shifting | Prevents "ear fatigue" and repetitiveness. |
| Dynamic Mixing | Ensures the music gets quieter when a "Super Move" starts. |
| RTPCs | Changes the sound of your footsteps based on health (e.g., limping). |

Are you looking to implement these variations in your code, or are you trying to organize your sound library for a project?

# ---

You're very welcome! We covered a lot of ground today—from hitbox shapes and Vec3 attributes to state machines and dynamic audio. It sounds like you're building a solid mental model for how professional fighting games and beat-'em-ups actually function under the hood.

Whenever you're ready to dive back in, we can look at frame data tables, input buffers, or even how to script a basic combo system.

Have a great rest of your day!

Would you like to save a summary of the technical terms we discussed for your notes?

# ---

Here is a summary of the technical game development concepts we covered, organized for your reference.

1. Hitbox Fundamentals

* Hitbox (Attackbox): The invisible area of an attack that deals damage or applies force upon contact.
* Hurtbox: The area of a character’s body that can receive damage. A "hit" occurs when an Attackbox overlaps a Hurtbox.
* Pushbox (Collision Box): The physical volume that prevents objects or characters from walking through each other or the environment. [1, 2]

2. Data Structures (Vec2 & Vec3)

* Vec2 (Shape): Commonly used to define the Width (X) and Height (Y) of the 2D hitbox polygon.
* Vec3 (Attributes): Often used to pack combat physics data:
* X: Base Strength/Damage.
   * Y: Launch Angle (0–360°).
   * Z: Knockback Scaling/Growth (how much force increases with victim damage).

3. State & Logic Management

* Gatekeeper Functions: Logical "checks" that must be true for an action to occur (e.g., Is_Throwable() or Is_Grounded()).
* State Machine: The "brain" that tracks what a character is doing (Idle, Attacking, Hitstun). It is strictly logic-based and changes instantly.
* Grabbox Logic: A specialized hitbox that, upon success, "parents" the victim to the attacker, synchronizing their positions until a throw is executed. [3, 4, 5, 6]

4. Technical Systems

* Double Buffering: A rendering technique that draws frames in a back buffer to prevent screen tearing, typically adding one frame of input lag.
* Input Buffer: A system that "remembers" player inputs for a few frames, making the game feel more responsive and forgiving.
* Audio Middleware: Advanced systems (like FMOD or [Wwise](https://sfxengine.com/blog/how-game-sound-effects-are-made)) that use parameters like Pitch Randomization and Hit-stop Scaling to make sounds feel dynamic rather than repetitive.

If you ever need to dive back into frame-by-frame logic or combo scripting, just let me know. Happy developing!

[1] [https://www.scribd.com](https://www.scribd.com/document/724823017/QUARTER-4-WEEK-1-TECHNICAL-TERMS-IN-RESEARCH)
[2] [https://www.scribd.com](https://www.scribd.com/document/722280507/technical-terms-used-in-research)
[3] [https://www.scribd.com](https://www.scribd.com/presentation/673239921/Technical-Terms-Used-in-Research)
[4] [https://www.slideshare.net](https://www.slideshare.net/slideshow/technical-terms-used-in-research/240234047)
[5] [https://www.scribd.com](https://www.scribd.com/document/445692418/Lesson-1-technical-terms-in-research-1)
[6] [https://www.scribd.com](https://www.scribd.com/document/506728972/Technical-Terms-in-Research-Grade-10)
