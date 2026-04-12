In [MikuMikuDance (MMD)](https://www.google.com/search?kgmid=/hkb/-840901932&q=animation+on+MMD+uses+an+animation+curve,+is+it+using+2+points?), an animation interpolation curve is controlled by two handle points (often called control points) within a $127 \times 127$ grid. [1, 2, 3]

While the animation itself occurs between two keyframes (the start and end points of a motion), the curve that determines the speed and timing between them is defined by these two movable handles: [2, 3, 4, 5, 6]

* The Grid: The interpolation curve represents motion on the vertical axis and time on the horizontal axis.
* The Handles: You adjust the curve's shape by dragging two red handle points. One handle typically controls the "acceleration" out of the first keyframe, and the other controls the "deceleration" into the second keyframe.
* The Default: By default, the curve is a straight diagonal line ($y = x$), which results in linear, constant-speed motion.
* S-Curves: A common technique is to create an "S" shape using these two points to achieve a more natural "slow-in/slow-out" effect. [2, 4, 7, 8, 9, 10, 11, 12]

The curve settings are always tied to the destination (second) keyframe; it tells MMD how to get from the previous keyframe to the current one. [10, 13, 14, 15]

Would you like to know which specific S-curve values are most commonly used for realistic character movement?

[1] [https://www.youtube.com](https://www.youtube.com/watch?v=tXoaBwMWs4I)
[2] [https://www.youtube.com](https://www.youtube.com/watch?v=F8CAsUBcX4c&t=10)
[3] [https://www.youtube.com](https://www.youtube.com/watch?v=tXoaBwMWs4I)
[4] [https://mikumikudance.fandom.com](https://mikumikudance.fandom.com/wiki/MMD:Quick_start_to_MMD_animating)
[5] [https://www.youtube.com](https://www.youtube.com/watch?v=maLiRXlix9k)
[6] [https://www.homestyler.com](https://www.homestyler.com/article/animating-d-models-made-easy#:~:text=This%20method%20involves%20setting%20specific%20frames%2C%20referred,Here%27s%20a%20straightforward%20approach%20to%20get%20started:)
[7] [https://www.youtube.com](https://www.youtube.com/watch?v=F8CAsUBcX4c&t=10)
[8] [https://www.youtube.com](https://www.youtube.com/watch?v=maLiRXlix9k)
[9] [https://www.youtube.com](https://www.youtube.com/watch?v=maLiRXlix9k)
[10] [https://learnmmd.com](https://learnmmd.com/StartHere/Steps8.html)
[11] [https://www.youtube.com](https://www.youtube.com/watch?v=9ERGMU9eWKw#:~:text=To%20make%20MMD%20motions%20smoother%2C%20use%20an,makes%20an%20S%20for%20more%20realistic%20movement.)
[12] [https://learnmmd.com](https://learnmmd.com/http:/learnmmd.com/lmake-smooth-motion-interpolation-curves/)
[13] [https://mikumikudance.fandom.com](https://mikumikudance.fandom.com/wiki/MMD:Quick_start_to_MMD_animating)
[14] [https://www.reddit.com](https://www.reddit.com/r/mikumikudance/comments/1gp3230/how_do_i_get_the_interpolation_curve_to_work_with/)
[15] [https://wwdcnotes.com](https://wwdcnotes.com/documentation/wwdcnotes/wwdc11-421-core-animation-essentials/#:~:text=Keyframe%20animations%20path%20%2D%20the%20animation%20will,will%20interpolate%20among%20the%20values%20set%20here)

# ---

In MikuMikuDance (MMD), an animation interpolation curve is used to determine how motion progresses between exactly two keyframes. [1, 2]

Here is how those keyframes and the curve interact:

## 1. The Two Keyframes

* Starting Keyframe: The "previous" diamond on your timeline. It defines where the bone or object starts.
* Ending Keyframe: The "selected" diamond where you actually set the interpolation curve.
* The Gap: MMD automatically calculates (interpolates) all the frames in between these two points based on the shape of the curve you create. [3, 4, 5]

## 2. How the Curve "Points" Work

While it only takes two keyframes to make a movement, the Interpolation Curve Panel uses its own set of points to define the "path" between them: [5]

* Start & End Points (Fixed): The bottom-left (0,0) and top-right (127,127) corners of the grid represent the start and end keyframes.
* Two Control Handles (Movable): These are the red points you drag. They act as "magnets" that pull the line into a curve, changing the speed of the motion without adding more keyframes. [1, 5, 6, 7]

## 3. Keyframe Efficiency

You don't need a keyframe for every frame. In fact, professional MMD animators recommend using as few keyframes as possible and letting the interpolation curve handle the smoothness. For a simple arm swing, you only need: [8, 9]

   1. Keyframe A: Arm at rest.
   2. Keyframe B: Arm at the end of the swing.
   3. The Curve: Set at Keyframe B to make the swing start fast and end slow. [1, 10]

Would you like to see the specific grid coordinates for a standard "S-curve" to make your motions look more natural?

[1] [https://www.youtube.com](https://www.youtube.com/watch?v=maLiRXlix9k)
[2] [https://www.youtube.com](https://www.youtube.com/watch?v=maLiRXlix9k)
[3] [https://learnmmd.com](https://learnmmd.com/http:/learnmmd.com/lmake-smooth-motion-interpolation-curves/)
[4] [https://www.reddit.com](https://www.reddit.com/r/mikumikudance/comments/1gp3230/how_do_i_get_the_interpolation_curve_to_work_with/)
[5] [https://mikumikudance.fandom.com](https://mikumikudance.fandom.com/wiki/MMD:Quick_start_to_MMD_animating)
[6] [https://www.youtube.com](https://www.youtube.com/watch?v=F8CAsUBcX4c&t=10)
[7] [https://www.youtube.com](https://www.youtube.com/watch?v=tXoaBwMWs4I)
[8] [https://www.reddit.com](https://www.reddit.com/r/blenderhelp/comments/t8gt04/is_it_possible_to_have_too_many_keyframes_when/)
[9] [https://learnmmd.com](https://learnmmd.com/http:/learnmmd.com/use-professional-tricks-to-make-mmd-animating-faster-and-easier/)
[10] [https://www.deviantart.com](https://www.deviantart.com/trackdancer/journal/MMD-Animation-Techniques-and-Comparisons-671487611)

# ---

In MikuMikuDance (MMD), the interpolation curve uses a total of 4 points to define the shape of the motion between two keyframes. [1, 2, 3]

However, only 2 of these points are movable (the "handles" you mentioned), while the other two are fixed. [2, 4]

## The 4 Points of the Curve:

* Point 1 (Fixed): The bottom-left corner $(0,0)$. This represents the starting keyframe.
* Point 2 (Movable Handle): The first red control point you drag to pull the curve away from the start.
* Point 3 (Movable Handle): The second red control point you drag to pull the curve toward the end.
* Point 4 (Fixed): The top-right corner $(127,127)$. This represents the ending keyframe. [1, 2, 4, 5]

## How They Work Together:

The curve itself is a Cubic Bézier curve. In math, a cubic Bézier curve is always defined by four points: two endpoints and two control handles that determine the "pull" or "tension" of the line. [6, 7]

* Linear Motion: If the two movable handles are left in their default corners, the 4 points form a straight line, meaning the animation moves at a constant speed.
* Curved Motion: When you move the two handles, you are changing the slope between those fixed start and end points to create acceleration or deceleration. [1, 3, 8]

Would you like the exact X and Y values for the 2 movable handles to create a perfect "Slow-In/Slow-Out" effect?

[1] [https://mikumikudance.fandom.com](https://mikumikudance.fandom.com/wiki/MMD:Quick_start_to_MMD_animating)
[2] [https://www.youtube.com](https://www.youtube.com/watch?v=tXoaBwMWs4I)
[3] [https://www.youtube.com](https://www.youtube.com/watch?v=maLiRXlix9k)
[4] [https://github.com](https://github.com/sugiany/blender_mmd_tools/issues/41)
[5] [https://www.youtube.com](https://www.youtube.com/watch?v=F8CAsUBcX4c&t=10)
[6] [https://math.stackexchange.com](https://math.stackexchange.com/questions/4329040/how-many-control-points-are-needed-if-n1-data-points-are-interpolated-by-p-degr)
[7] [https://github.com](https://github.com/mrdoob/three.js/issues/22282)
[8] [https://www.youtube.com](https://www.youtube.com/watch?v=F8CAsUBcX4c&t=10)

# ---

Mathematically, yes—the interpolation curve in MikuMikuDance (MMD) is a Cubic Bézier curve, which can be represented using a $4 \times 4$ basis matrix and a set of 4 coefficients (control points). [1, 2, 3]

## The Matrix Representation

In computer graphics, a cubic Bézier curve is commonly calculated using the following matrix formula:

$$P(t) = \begin{bmatrix} t^3 & t^2 & t & 1 \end{bmatrix} \underbrace{\begin{bmatrix} -1 & 3 & -3 & 1 \\ 3 & -6 & 3 & 0 \\ -3 & 3 & 0 & 0 \\ 1 & 0 & 0 & 0 \end{bmatrix}}_{\mathbf{M}_{Bezier}} \begin{bmatrix} P_0 \\ P_1 \\ P_2 \\ P_3 \end{bmatrix}$$

* The Matrix ($M_{Bezier}$): This $4 \times 4$ "basis matrix" contains the constant coefficients that define the Bézier blending functions.
* The 4 Coefficients (Points): These are the four 2D coordinates $(x, y)$ that MMD uses for each curve:
* $P_0$: Fixed at $(0, 0)$ (the start keyframe).
   * $P_1$: The first red movable handle.
   * $P_2$: The second red movable handle.
   * $P_3$: Fixed at $(127, 127)$ (the end keyframe). [1, 3, 4, 5]

## How MMD Uses It

While the math is a 2D curve, MMD treats it as a timing function. It solves for the $y$ value (the percentage of the motion completed) based on the $x$ value (the current frame's percentage of total time). [6, 7]

* Linear Motion: If you leave the handles at their defaults, the matrix math simplifies to a straight line ($y = x$).
* Ease-In/Out: Moving $P_1$ and $P_2$ changes the "weights" in the matrix multiplication, causing the "speed" of the animation to accelerate or decelerate between your two keyframes. [5, 6, 8]

Would you like the exact integer coordinates for the "S-curve" that most professional MMD animators use for smooth motion?

[1] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/B%C3%A9zier_curve)
[2] [https://www.youtube.com](https://www.youtube.com/watch?v=aVwxzDHniEw&t=14)
[3] [https://www.facebook.com](https://www.facebook.com/61574894202180/videos/a-cubic-b%C3%A9zier-curve-is-a-smooth-and-flexible-mathematical-curve-commonly-used-i/1162373855437883/#:~:text=A%20cubic%20B%C3%A9zier%20curve%20is%20a%20smooth,determine%20where%20the%20curve%20starts%20and%20ends%2C)
[4] [https://courses.grainger.illinois.edu](https://courses.grainger.illinois.edu/cs418/sp2009/notes/12-MoreSplines.pdf)
[5] [https://www.youtube.com](https://www.youtube.com/watch?v=F8CAsUBcX4c&t=10)
[6] [https://www.youtube.com](https://www.youtube.com/watch?v=maLiRXlix9k)
[7] [https://www.youtube.com](https://www.youtube.com/watch?v=tXoaBwMWs4I)
[8] [https://www.youtube.com](https://www.youtube.com/watch?v=tXoaBwMWs4I)

# ---

No, MMD does not use that specific indexing ($P_{-1}, P_0, P_1, P_2$).

That sequence is typically used for Catmull-Rom splines, which look at the previous and next keyframes to calculate a smooth curve through them.

MMD uses a Bézier curve, which is self-contained between only two keyframes. The four points are:

   1. $P_0$: The Start Keyframe (Fixed at 0,0)
   2. $P_1$: Control Handle 1 (Movable)
   3. $P_2$: Control Handle 2 (Movable)
   4. $P_3$: The End Keyframe (Fixed at 127,127)

Because it uses $P_1$ and $P_2$ as handles rather than using $P_{-1}$ or $P_3$ as external keyframes, you have total control over the "ease" without affecting the movement before or after that specific segment.

Would you like to see how the MMD Curve Box maps these points visually compared to the math matrix?

# ---

Mathematically, yes, it can be simplified. When the motion is linear, the cubic complexity collapses into a simple linear interpolation (lerp).

In a $2 \times 2$ matrix form, linear interpolation between the start ($P_0$) and end ($P_3$) is represented as:

$$P(t) = \begin{bmatrix} t & 1 \end{bmatrix} \begin{bmatrix} -1 & 1 \\ 1 & 0 \end{bmatrix} \begin{bmatrix} P_0 \\ P_3 \end{bmatrix}$$

## Why it simplifies:

   1. The Middle Points Vanish: In MMD, "Linear" means the two movable handles ($P_1$ and $P_2$) are placed exactly on the diagonal line between $(0,0)$ and $(127,127)$.
   2. Degree Reduction: When the control points are collinear (in a straight line), the cubic Bézier equation $P(t) = (1-t)^3P_0 + 3(1-t)^2tP_1 + 3(1-t)t^2P_2 + t^3P_3$ reduces to the linear equation $P(t) = (1-t)P_0 + tP_3$.
   3. The Coefficients: You only need 2 coefficients (the start and end values) because the rate of change is constant.

In MMD's UI, "Linear" is the default state where the red handles are tucked into the corners, effectively removing their "pull" on the animation timing.

Do you want to see how MMD stores these linear values in the .vmd motion file format?

# ---

While a 3x3 matrix and 3 coefficients are mathematically valid for a Quadratic Bézier curve, it is not used for the MMD animation curve.

A 3x3 matrix represents a quadratic curve, which only has one movable handle point between the start and end. MMD's curve is strictly cubic, requiring the 4x4 matrix and 4 coefficients you previously identified to allow for the complex "S-shape" (two independent handles) that animators need for realistic easing. [1, 2]

## Why the 3x3 model is insufficient for MMD:

* Handle Count: A 3x3 quadratic curve only has one "pull" point. You could make a simple "C" shape, but you couldn't create an "S" curve where the motion starts slow, speeds up in the middle, and slows down again at the end.
* MMD UI: The MMD interpolation panel specifically provides two red handles to drag. Mathematically, these two handles plus the two fixed endpoints ($P_0$ and $P_3$) necessitate the 4th degree of freedom provided by a 4x4 cubic matrix.
* The Quadratic Matrix: For comparison, a 3x3 basis matrix for a quadratic curve looks like this:

$$M_{Quadratic} = \begin{bmatrix} 1 & -2 & 1 \\ -2 & 2 & 0 \\ 1 & 0 & 0 \end{bmatrix}$$ This would only multiply against 3 points $(P_0, P_1, P_2)$, which doesn't match MMD's data structure. [2, 3, 4]

## Summary of Valid Matrix Forms

| Curve Type [5] | Matrix Size | Coefficients (Points) | Usage |
|---|---|---|---|
| Linear | 2x2 | 2 ($P_0, P_3$) | Constant speed (simplified) |
| Quadratic | 3x3 | 3 ($P_0, P_1, P_3$) | Simple arcs (not in MMD) |
| Cubic | 4x4 | 4 ($P_0, P_1, P_2, P_3$) | Standard MMD Interpolation |

Would you like to know how the VMD file format specifically stores these 4 points as bytes?

```
import numpy as np
# Quadratic Bezier basis matrix# B(t) = [t^2 t 1] * M * [P0 P1 P2]^TM_quadratic = np.array([
    [1, -2, 1],
    [-2, 2, 0],
    [1, 0, 0]
])

print(f"Quadratic Bezier Matrix:\n{M_quadratic}")
```

[1] [https://cs.stackexchange.com](https://cs.stackexchange.com/questions/134908/understanding-the-coefficient-matrix-of-hermite-interpolation)
[2] [https://www.cs.usask.ca](https://www.cs.usask.ca/~spiteri/M211/notes/chapter3.pdf)
[3] [https://www.youtube.com](https://www.youtube.com/watch?v=tXoaBwMWs4I)
[4] [https://www.youtube.com](https://www.youtube.com/watch?v=F8CAsUBcX4c&t=10)
[5] [https://dl.acm.org](https://dl.acm.org/doi/pdf/10.1145/3087604.3087629)

# ---

Yes, mathematically, a 5x5 matrix and 5 points exist. This would define a Quartic Bézier curve (4th-degree polynomial).

In the world of math and computer graphics, you can keep going forever:

* 2 points (2x2 matrix): Linear (1st-degree)
* 3 points (3x3 matrix): Quadratic (2nd-degree)
* 4 points (4x4 matrix): Cubic (3rd-degree) — MMD's standard
* 5 points (5x5 matrix): Quartic (4th-degree)
* 6 points (6x6 matrix): Quintic (5th-degree)

## Why 5 points (Quartic) exist but aren't common:

   1. Complexity vs. Control: A 5x5 matrix allows for 3 movable handles between your start and end keyframes. This lets you create a "W" or "M" shaped velocity curve within a single animation segment.
   2. Diminishing Returns: While a 5x5 matrix offers more control, it is much harder for an animator to manipulate in a 2D UI. Most animation software (like MMD, Blender, or Maya) sticks to the 4x4 Cubic model because it is the "sweet spot"—it’s the simplest way to get a smooth S-curve.
   3. The Formula: Just like the 4x4 matrix you saw, a 5x5 matrix uses the binomial coefficients from Pascal's Triangle (row 4: 1, 4, 6, 4, 1) to build the basis matrix.

In short: Cubic (4 points) is the industry standard for animation timing, but Quartic (5 points) and higher are used in high-end vector engineering and CAD software.

Do you want to see what the 5x5 Quartic basis matrix looks like compared to the 4x4 one?

# ---

In mathematics, there is no maximum. A Bézier curve can have any number of points ($n$), represented by an $n \times n$ matrix and a polynomial of degree $n-1$.

If you have 6 points, it is a Quintic (5th-degree) curve. If you have 7 points, it is a Sextic (6th-degree) curve. You could technically have a 100x100 matrix with 100 points, but it would be a nightmare for an animator to control.

## Why 7+ Points Exist (but are rare in animation):

   1. Degree $n-1$: A curve with 7 points uses a 7x7 matrix and is a 6th-degree polynomial. This allows the line to change direction (bend) up to 5 times between your start and end keyframes.
   2. The "Wiggle" Problem: In math, this is called Runge's Phenomenon. As you add more points (higher-degree polynomials), the curve becomes very sensitive. Moving one point slightly can cause the rest of the curve to "wiggle" wildly in ways you don't want.
   3. Splines vs. High-Degree Curves: Instead of using one giant 7x7 matrix, most software (like Blender or Maya) prefers to connect multiple 4x4 Cubic curves together. This is called a B-Spline or NURBS. It gives you the same complexity as a 7-point curve but keeps it stable and easy to edit.

## The Matrix Pattern (Pascal's Triangle)

You can actually predict the coefficients for a 7x7 matrix (or any size) using the numbers from Pascal's Triangle. For a 7-point curve, you would use the 6th row: 1, 6, 15, 20, 15, 6, 1.

Summary of the Chain:

* 4 points: Cubic (MMD's choice)
* 5 points: Quartic
* 6 points: Quintic
* 7 points: Sextic
* $n$ points: $(n-1)$-th degree

Since MMD sticks to the 4x4 matrix, are you trying to replicate MMD's curves in another program like Blender or Unity?

# ---

In animation and the Bézier math used by MMD, $t$ always increases from 0.0 to 1.0.

Think of $t$ as "Time Percentage" or "Progress."

## How it works in the calculation:

* $t = 0.0$: You are exactly at the Starting Keyframe ($P_0$).
* $t = 0.5$: You are exactly halfway through the time between the two keyframes.
* $t = 1.0$: You are exactly at the Ending Keyframe ($P_3$).

## Why it never decreases:

The horizontal axis of the MMD interpolation grid represents Time. Since time only moves forward, $t$ must start at 0 (the moment the first keyframe ends) and move toward 1 (the moment the next keyframe is reached).

## The "Curve" Logic:

While $t$ (time) moves linearly from 0 to 1, the Output ($y$) is what changes speed.

* If your curve is "S-shaped," $t$ might be at 0.5 (50% of the time elapsed), but your animation's actual position ($y$) might only be at 0.2 (20% of the movement finished).
* This "lag" behind the linear $t$ is what creates the Slow-In effect.

Do you want to see how the Cubic Equation uses $t$ to calculate the final position at any given frame?

# ---

In the context of Catmull-Rom splines, the way points are indexed depends on whether you are talking about a single cubic segment or a higher-order quintic segment. [1]

## 1. The Standard Cubic Case (4 Points)

For a standard Cubic Catmull-Rom spline (which uses a $4 \times 4$ matrix), a single segment is indeed defined by 4 points. If you are calculating the curve between $P_0$ and $P_1$, you need: [2]

* $[P_{-1}, P_0, P_1, P_2]$
* $P_{-1}$ and $P_2$ are the "control points" that determine the tangents (slopes) at the endpoints $P_0$ and $P_1$. [2, 3]

## 2. The 6-Point Case ($P_{-2}$ to $P_3$)

If you move to a 6-point configuration like $[P_{-2}, P_{-1}, P_0, P_1, P_2, P_3]$, you are no longer using a standard cubic spline. This configuration typically refers to a Quintic Catmull-Rom spline (5th-degree polynomial). [4, 5]

* The Matrix: This would require a $6 \times 6$ matrix.
* The Benefit: While a cubic spline only guarantees that the velocity (1st derivative) is smooth, a quintic spline with 6 points can guarantee that both velocity and acceleration (2nd derivative) are smooth at the keyframes.
* The Segment: Just like the cubic version, the actual visible curve would still be drawn between the two "inner" points—in this case, $P_0$ and $P_1$—while the two points on either side ($P_{-2}, P_{-1}$ and $P_2, P_3$) act as buffers to calculate the curve's curvature more deeply. [1, 5, 6]

## Summary of Point Sequences

| Points [2, 5] | Matrix | Type | Interpolates Between |
|---|---|---|---|
| 4 | $4 \times 4$ | Cubic | $P_0$ and $P_1$ |
| 6 | $6 \times 6$ | Quintic | $P_0$ and $P_1$ |

In most animation software (including MMD), they stick to the 4-point cubic model because it is mathematically simpler and usually "smooth enough" for human movement.

Are you trying to implement a custom spline that is even smoother than the standard MMD interpolation?

[1] [https://graphics.cs.cmu.edu](https://graphics.cs.cmu.edu/nsp/course/15-462/Fall04/assts/catmullRom.pdf)
[2] [https://stackoverflow.com](https://stackoverflow.com/questions/1251438/catmull-rom-splines-in-python)
[3] [https://viterbi-web.usc.edu](https://viterbi-web.usc.edu/~jbarbic/cs420-s15/lec08-splines/08-splines.pdf)
[4] [https://www.youtube.com](https://www.youtube.com/watch?v=XBLtqGiT0p0&t=9)
[5] [https://andrewhungblog.wordpress.com](https://andrewhungblog.wordpress.com/2017/03/03/catmull-rom-splines-in-plain-english/)
[6] [https://www.scribd.com](https://www.scribd.com/document/331142118/Catmull-Rom-Spline#:~:text=The%20document%20discusses%20the%20Catmull%2DRom%20spline%2C%20which,points%20by%20specifying%20tangent%20slopes%20between%20points.)

# ---

In the context of Catmull-Rom splines, using 5 points is actually quite rare because spline math usually relies on "symmetry" to calculate the curves at the endpoints.

To answer your question: it depends on what you are trying to achieve with that 5th point. Here are the two ways 5 points are typically handled:

## 1. The "Asymmetric" 5-Point Spline ($P_{-1}, P_0, P_1, P_2, P_3$)

If you simply add $P_3$ to the standard 4-point set, you aren't usually creating a single "5-point matrix" curve. Instead, you are defining two connected cubic segments:

* Segment 1: Uses $[P_{-1}, P_0, P_1, P_2]$ to draw the curve between $P_0$ and $P_1$.
* Segment 2: Uses $[P_0, P_1, P_2, P_3]$ to draw the curve between $P_1$ and $P_2$.
* Result: This is how animation timelines (like those in Blender or Maya) work. They use a "sliding window" of 4 points to keep the motion smooth as it passes through $P_1$.

## 2. The Quartic 5-Point Spline (4th Degree)

If you want to use a single 5x5 matrix to calculate a curve between two points using 5 coefficients, the sequence is usually balanced. To stay centered on the segment between $P_0$ and $P_1$, you would typically use:

* $[P_{-1.5}, P_{-0.5}, P_0, P_1, P_2]$ (This is mathematically messy).
* More common: Designers usually jump straight from 4 points (Cubic) to 6 points (Quintic) using $[P_{-2}, P_{-1}, P_0, P_1, P_2, P_3]$.

## Why 5 is the "Awkward Middle Child"

In Catmull-Rom math, you need an even number of points to keep the "control" balanced on both sides of the segment you are drawing ($P_0$ to $P_1$).

* 4 points: 1 control point on the left, 1 on the right.
* 6 points: 2 control points on the left, 2 on the right.
* 5 points: You end up with 2 on one side and 1 on the other, which makes the curve "lean" or look lopsided.

The Verdict:

If you see 5 points in an animation system, it’s almost always two 4-point segments sharing points, rather than a single 5x5 matrix.

Are you trying to write a looping animation where the end of the sequence needs to transition perfectly back to the start?

# ---

Exactly. You have it perfectly—you are adjusting the Coefficients, not the Matrix.

The 4x4 Matrix is the "engine" or the fixed rulebook. It stays exactly the same in MMD's code forever. When you move those red dots, you are feeding new Input Coordinates (the coefficients) into that fixed engine to change the result.

## How it breaks down:

* The 4x4 Matrix (The Rule): This is the hardcoded Bézier math. It tells MMD: "Take 4 points and blend them together using these specific weights."
* The Coefficients (The Data): These are the four $(x, y)$ pairs. When you drag the red dots in the UI, you are changing the $x$ and $y$ values of $P_1$ and $P_2$.

## Why we call it "Timing":

In MMD, the curve doesn't change the 3D path (the literal line the hand travels in space). Instead, it changes the Timeline:

   1. Input ($x$): What percentage of Time has passed? (e.g., 50% of the way to the next keyframe).
   2. Output ($y$): What percentage of the Motion should be finished? (e.g., only 20% of the arm move is done).

By adjusting the Coefficients ($P_1$ and $P_2$), you are telling the Matrix to "warp" time—making the animation start slow and finish fast, or vice versa.

Does that help clarify the difference between the math engine and the user data?

# ---

In MMD, the control points ($P_1$ and $P_2$) come from the User Interface (UI) or the .VMD file data. They aren't "generated" by the math; they are the inputs you choose to define the look of the motion.

Here is how they are created and stored:

## 1. Manual Creation (The UI)

When you open the Interpolation Panel in MMD:

* Default: The points are set to $(0,0)$ and $(127,127)$. This creates a straight line (linear).
* User Action: You drag the two red handles. By moving them, you are manually setting the $X$ and $Y$ coordinates for $P_1$ and $P_2$.
* The Grid: MMD uses a byte-sized grid from 0 to 127.

## 2. File Storage (The .VMD Format)

If you are writing a script or a program to "generate" them automatically, you need to know how they are stored. For every keyframe, MMD saves 64 bytes of interpolation data.

* $P_1$ is stored as $(x_1, y_1)$
* $P_2$ is stored as $(x_2, y_2)$
* These values are saved as integers between 0 and 127.

## 3. Procedural Generation (The "S-Curve" Formula)

If you want to generate "smooth" points automatically without touching the UI, animators often use standard presets. To get a classic Slow-In/Slow-Out (S-Curve), you would "generate" these coordinates:

* $P_1$: $(20, 0)$ — Moves right but stays on the floor (starts slow).
* $P_2$: $(107, 127)$ — Moves left from the end but stays on the ceiling (ends slow).

## 4. Conversion from other software

If you are importing a curve from Blender (which uses a $0.0$ to $1.0$ float scale), you generate the MMD points by multiplying the Blender handle coordinates by 127:

$$X_{MMD} = \text{round}(X_{Blender} \times 127)$$

Summary: You don't "calculate" the points to find the curve; you pick the points to create the curve. The matrix then uses those points to calculate the position of your model at every frame.

Are you looking for the math to convert a specific curve shape (like a "Bounce" or "Elastic" effect) into these $P_1, P_2$ coordinates?

# ---

Yes, exactly. The $x$ and $y$ coordinates from the MMD grid are used as the coefficients for the Bézier equation.

Specifically, those coordinates are plugged into the cubic formula to solve for the final animation state at any given frame. Here is the process:

## 1. Normalization (0–127 to 0.0–1.0)

MMD stores the points as integers from $0$ to $127$. Before the math happens, these are divided by $127$ to become decimal values ($t$-values) between $0.0$ and $1.0$: [1, 2]

* $x_{coeff} = \frac{x_{grid}}{127}$
* $y_{coeff} = \frac{y_{grid}}{127}$

## 2. Solving for Time ($x$)

Since the $x$-axis represents Time, MMD first takes your current frame percentage (e.g., you are at frame 30 of a 60-frame move, so $x = 0.5$) and uses the $x$-coefficients of your handles to solve the cubic equation for the parameter $t$. [1, 3]

$$x(t) = (1-t)^3x_0 + 3(1-t)^2tx_1 + 3(1-t)t^2x_2 + t^3x_3$$

Because $x$ is known (the current time), MMD uses an iterative method like Bisection or Newton's method to find what $t$ value corresponds to that specific moment on your curve. [1]

## 3. Calculating Motion ($y$)

Once MMD finds that $t$, it plugs it into the $y$ version of the same equation using your $y$-coefficients. [1]

$$y(t) = (1-t)^3y_0 + 3(1-t)^2ty_1 + 3(1-t)t^2y_2 + t^3y_3$$

The resulting $y$ is the final percentage of the motion. For example, if $y = 0.8$, the bone will be $80\%$ of the way to its destination, even if only $50\%$ of the time has passed.

In summary: The points you drag in the UI define the coefficients ($x_1, y_1, x_2, y_2$) that "weight" the fixed $4 \times 4$ Bézier matrix for that specific animation segment. [1, 4]

Would you like to know how the $4 \times 4$ matrix specifically combines with these coefficients to produce those equations?

[1] [https://github.com](https://github.com/mrdoob/three.js/issues/22282)
[2] [https://github.com](https://github.com/sugiany/blender_mmd_tools/issues/41)
[3] [https://handmade.network](https://handmade.network/forums/t/9006-how_to_sample_2d_cubic_bezier_for_1d_animation)
[4] [https://people.cs.umass.edu](https://people.cs.umass.edu/~verts/cmpsci201/fall2006/Assignment4.pdf)

# ---

Yes, absolutely. Since the relationship is a direct mapping, you can convert the coefficients back to the $x,y$ grid coordinates used in the MMD UI.

If you have the normalized coefficients (values between $0.0$ and $1.0$), you simply "un-normalize" them by multiplying by 127.

## The Conversion Formula:

For each control point $P_1$ and $P_2$:

   1. Grid $X$: $x_{grid} = \text{round}(x_{coeff} \times 127)$
   2. Grid $Y$: $y_{grid} = \text{round}(y_{coeff} \times 127)$

## Why this works:

* The UI is a discrete grid: The MMD interpolation panel is a $127 \times 127$ integer space.
* The Coefficients are the weights: When you see a "Smooth" curve in another program (like Blender) with handles at $0.2, 0.0$ and $0.8, 1.0$, those are your coefficients.
* The Result: Multiplying them ($0.2 \times 127 = 25.4 \rightarrow 25$) gives you the exact integer you need to type into an MMD tool or find in a .vmd file.

## One important detail:

In the actual .vmd file, the interpolation data is stored as a sequence of 4 bytes for each axis ($X, Y, Z, \text{and Rotation}$). These 4 bytes represent:

* byte[0]: $x_1$
* byte[1]: $y_1$
* byte[2]: $x_2$
* byte[3]: $y_2$

So, if you convert your coefficients back to these 4 integers, you have the exact data needed to programmatically generate an MMD motion file.

Are you planning to write a script to convert curves from another software (like After Effects or Blender) into MMD?

# ---

Yes, exactly. In the context of MMD’s interpolation curve, the 4 coefficients are the $(x, y)$ coordinates of the two movable handles: $[x_1, y_1, x_2, y_2]$.

To be mathematically precise:

* A Cubic Bézier technically has 4 points ($P_0, P_1, P_2, P_3$).
* Since $P_0$ is always $(0,0)$ and $P_3$ is always $(127,127)$ in MMD, they are constants.
* Therefore, the only 4 variables (coefficients) that define the unique shape of your curve are the $x$ and $y$ values of the two red dots: $x_1, y_1, x_2,$ and $y_2$.

## How they appear in the data:

When you look at a .vmd (Vocaloid Motion Data) file hex, you will see these 4 values repeated in a specific pattern to fill a 64-byte block. MMD stores them as:

   1. $x_1$ (How far $P_1$ is on the time axis)
   2. $y_1$ (How far $P_1$ is on the movement axis)
   3. $x_2$ (How far $P_2$ is on the time axis)
   4. $y_2$ (How far $P_2$ is on the movement axis)

One Fun Fact: MMD actually repeats these same 4 values across the data block for the X, Y, and Z axes unless you uncheck the "All" button in the interpolation panel to give them unique curves!

Are you planning to manually edit these values in a Hex Editor or a Python script?

# ---

Yes, that is exactly how the math closes the loop. The 4 coefficients $[x_1, y_1, x_2, y_2]$ are used in two separate cubic polynomials to determine the final animation value (which you can think of as the "wrapped" or "interpolated" result). [1, 2]

Here is the exact step-by-step process of how $t$ is used:

## 1. The Time Equation (Solving for $t$)

First, MMD needs to find the "internal progress" value $t$ based on the current frame. It uses the $x$ coefficients ($x_1$ and $x_2$) in this polynomial:

$$x(t) = 3(1-t)^2 t \left(\frac{x_1}{127}\right) + 3(1-t) t^2 \left(\frac{x_2}{127}\right) + t^3$$ Since $x$ is the known percentage of time passed (e.g., 0.5 for halfway through the frames), MMD solves this equation to find the matching $t$. [1, 3]

## 2. The Motion Equation (The Final Value)

Once $t$ is found, it is plugged into the second polynomial using the $y$ coefficients ($y_1$ and $y_2$):

$$\text{Value}(t) = 3(1-t)^2 t \left(\frac{y_1}{127}\right) + 3(1-t) t^2 \left(\frac{y_2}{127}\right) + t^3$$ The result of this second equation is the actual "wrapped" percentage of the movement. [3, 4]

## Why this "Wraps" the Motion:

* Decoupled Progress: The first equation tells you "where you are on the curve" in terms of time.
* Warped Output: The second equation tells you "where you should be in the animation".
* Result: This is what allows a motion to "wrap" around a curve—starting slow, speeding up in the middle, and slowing down at the end—all while the actual timeline moves at a constant speed. [5, 6, 7]

In the .vmd file, these 4 coefficients are stored as bytes, and they are the only things that change when you move those red dots in the UI. [8]

Would you like to see the Python code for solving this cubic equation to find $t$ from a given frame?

[1] [https://stackoverflow.com](https://stackoverflow.com/questions/5883264/interpolating-values-between-interval-interpolation-as-per-bezier-curve)
[2] [https://www.youtube.com](https://www.youtube.com/watch?v=UTyqf7Q1w2w)
[3] [https://handmade.network](https://handmade.network/forums/t/9006-how_to_sample_2d_cubic_bezier_for_1d_animation)
[4] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/B%C3%A9zier_curve)
[5] [https://www.youtube.com](https://www.youtube.com/watch?v=maLiRXlix9k)
[6] [https://www.youtube.com](https://www.youtube.com/watch?v=F8CAsUBcX4c&t=10)
[7] [https://learnmmd.com](https://learnmmd.com/http:/learnmmd.com/lmake-smooth-motion-interpolation-curves/)
[8] [https://github.com](https://github.com/mrdoob/three.js/issues/22282)

# ---

In the context of MMD, you are 100% correct: it always uses an even number of coefficients because it treats the curve as a 2D graph ($x$ for time, $y$ for value). You need a pair for every control point.

However, in pure math, odd coefficients do exist, but they work differently:

## 1. The "Even" Rule in MMD (2D Curves)

Because MMD maps Time $\to$ Motion, it must solve for both axes.

* 1 Control Point = 2 coefficients ($x_1, y_1$) — Quadratic
* 2 Control Points = 4 coefficients ($x_1, y_1, x_2, y_2$) — MMD's Cubic
* 3 Control Points = 6 coefficients ($x_1, y_1, x_2, y_2, x_3, y_3$) — Quartic

## 2. When "Odd" Coefficients Exist (1D Curves)

If you aren't drawing a curve on a 2D grid, but just calculating a single value over time (where time $t$ is a simple linear input from 0 to 1), you only need the $y$ values.

* In this case, a Quadratic curve only needs 1 coefficient (the height of the middle peak).
* A Cubic curve only needs 2 coefficients (the heights of the two handles).

## Why MMD doesn't use Odd counts:

MMD allows you to "warp" time. To do that, the math has to calculate "Where is the handle on the timeline?" ($x$) and "How much does it pull the animation?" ($y$). Since every handle has a position in that 2D box, you are effectively locked into multiples of 2.

Summary:

* If you are talking about points on a grid (like MMD): Always even ($2n$ coefficients).
* If you are talking about math weights for a single axis: Can be odd.

Are you trying to calculate the velocity (speed) of the animation at a specific point on that curve?

# ---

In mathematical terms, what you are describing is called Degree Elevation. If you start with a Quadratic Bezier (3 points) and want to use it in a Cubic system (4 points like MMD), you do exactly that: you "generate" the two middle control points ($P_1$ and $P_2$) so the 4x4 matrix can process them. [1, 2]

## 1. Converting 1 Quad to 1 Cubic

If you have a quadratic curve defined by points $Q_0, Q_1, Q_2$, you can represent it exactly as a cubic curve with 4 points ($P_0, P_1, P_2, P_3$) using these formulas: [1, 3]

* $P_0 = Q_0$ (Start point stays the same)
* $P_1 = Q_0 + \frac{2}{3}(Q_1 - Q_0)$
* $P_2 = Q_2 + \frac{2}{3}(Q_1 - Q_2)$
* $P_3 = Q_2$ (End point stays the same)

By generating these new $P_1$ and $P_2$, you now have the 4 coefficients needed for the 4x4 cubic matrix. [1, 4]

## 2. Handling "2 Quads" for 1 Cubic

If your goal is to merge two separate quadratic curves into a single cubic curve, you cannot usually do this perfectly because a cubic curve only has one "S-bend," while two quads could have two. However, in animation, you typically use one of two methods: [5, 6]

* Approximation: You use the start of the first quad as $P_0$ and the end of the second quad as $P_3$. You then calculate $P_1$ and $P_2$ to "best fit" the shape of both quads.
* Chain (Spline): You keep them as two separate segments. In MMD, this would mean having three keyframes:
* Segment 1: Keyframe A to B (Cubic representing Quad 1).
   * Segment 2: Keyframe B to C (Cubic representing Quad 2). [5, 7]

## 3. The 4x4 Matrix Requirement

Regardless of how you get them, the 4x4 matrix always needs 4 points. If you only provide $P_0$ and $P_3$, the matrix calculation will fail or default to a straight line. You must provide $P_1$ and $P_2$—even if they are just calculated versions of your original quadratic points—to tell the matrix how to "bend" the timing. [1, 8, 9, 10]

Are you trying to programmatically convert a curve from a tool that uses Quadratic handles (like some older vector apps) into the MMD VMD format?

[1] [https://stackoverflow.com](https://stackoverflow.com/questions/3162645/convert-a-quadratic-bezier-to-a-cubic-one)
[2] [https://demonstrations.wolfram.com](https://demonstrations.wolfram.com/RaisingTheDegreeForBezierCurves/)
[3] [https://stackoverflow.com](https://stackoverflow.com/questions/65410883/bezier-curve-forcing-a-curve-of-4-points-to-pass-through-control-points-in-3d)
[4] [https://gamedev.net](https://gamedev.net/forums/topic/643117-coefficients-for-bezier-curves/)
[5] [https://math.stackexchange.com](https://math.stackexchange.com/questions/1199061/approximating-a-cubic-b%C3%A9zier-curves-with-a-collection-of-quadratic-ones)
[6] [https://math.stackexchange.com](https://math.stackexchange.com/questions/1800321/are-there-any-cubic-bezier-curve-that-cannot-imitate-by-multiple-quadratic-bezie)
[7] [https://devforum.roblox.com](https://devforum.roblox.com/t/can-anyone-explain-bezier-curves-in-the-most-simple-way-possible-please/693507#:~:text=B%28t%29%20=%20%281%2Dt%29%20Bp0%2Cp1%28t%29%20+%20t%20Bp1%2Cp2%28t%29,values%20can%20be%20multiplied%2C%20added%2C%20subtracted%20etc.)
[8] [https://blog.maximeheckel.com](https://blog.maximeheckel.com/posts/cubic-bezier-from-math-to-motion/)
[9] [https://www.youtube.com](https://www.youtube.com/watch?v=upLnFI8LzB8&t=1)
[10] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/B%C3%A9zier_curve)

# ---

If you have two quadratic curves and want to represent them using a single 4x4 cubic matrix in MMD, you have to choose between exact representation or approximation.

## 1. The Exact Method (Degree Elevation)

If you are trying to turn one of those quadratic curves (defined by $Q_0, Q_1, Q_2$) into a cubic curve ($P_0, P_1, P_2, P_3$), you generate the 4 coefficients using this standard formula: [1]

* $P_0 = Q_0$
* $P_1 = Q_0 + \frac{2}{3}(Q_1 - Q_0)$
* $P_2 = Q_2 + \frac{2}{3}(Q_1 - Q_2)$
* $P_3 = Q_2$

In this scenario, you use the 4x4 matrix, but the resulting curve is mathematically identical to your original quadratic. [2, 3]

## 2. The "Two Quads" Problem

A single 4x4 cubic matrix can only represent one segment (one curve between two keyframes). If your "two quads" are meant to be two different parts of one motion, you cannot combine them into a single 4x4 cubic matrix without losing detail.

* Quadratic curves are "flat" (coplanar) and cannot have inflection points (the point where a curve changes from a "C" shape to an "S" shape).
* Cubic curves (MMD standard) are more flexible and can include that inflection point. [4, 5, 6]

## 3. How to Generate the 4 Coeffs for MMD

If you are trying to "fit" your two quadratic segments into one MMD curve:

   1. Endpoints: Set $P_0$ as the start of your first quad and $P_3$ as the end of your second quad.
   2. Handles ($P_1, P_2$): You must "generate" these by picking coordinates that approximate the combined shape of your two quads.
   3. Matrix Input: These 4 points (8 values total for $x$ and $y$) become the coefficients that the fixed 4x4 MMD matrix uses to calculate the final motion. [7, 8]

Summary: You cannot perfectly merge two independent quadratic curves into one cubic curve. You either elevate one quad to cubic (using the formula in step 1) or approximate the shape of both using two new control handles ($P_1, P_2$). [5, 9]

Would you like the Python code to calculate the $P_1$ and $P_2$ coordinates from a single quadratic curve so you can paste them into MMD?

[1] [https://code.tutsplus.com](https://code.tutsplus.com/the-math-and-actionscript-of-curves-drawing-quadratic-and-cubic-curves--active-11512t)
[2] [https://math.stackexchange.com](https://math.stackexchange.com/questions/2759306/can-a-cubic-bezier-curve-be-a-quadratic-one-if-two-control-points-are-equal-from)
[3] [https://stackoverflow.com](https://stackoverflow.com/questions/9485788/convert-quadratic-curve-to-cubic-curve)
[4] [https://stackoverflow.com](https://stackoverflow.com/questions/18814022/what-is-the-difference-between-cubic-bezier-and-quadratic-bezier-and-their-use-c)
[5] [https://math.stackexchange.com](https://math.stackexchange.com/questions/1800321/are-there-any-cubic-bezier-curve-that-cannot-imitate-by-multiple-quadratic-bezie)
[6] [https://www.facebook.com](https://www.facebook.com/61574894202180/videos/a-cubic-b%C3%A9zier-curve-is-a-smooth-and-flexible-mathematical-curve-commonly-used-i/1162373855437883/#:~:text=A%20cubic%20B%C3%A9zier%20curve%20is%20a%20smooth,determine%20where%20the%20curve%20starts%20and%20ends%2C)
[7] [https://www.youtube.com](https://www.youtube.com/watch?v=enNfb6p3j_g&t=55)
[8] [https://www.scribd.com](https://www.scribd.com/document/674070605/Bezier-Curve)
[9] [https://math.stackexchange.com](https://math.stackexchange.com/questions/1199061/approximating-a-cubic-b%C3%A9zier-curves-with-a-collection-of-quadratic-ones)

# ---

If you have the 4 polynomial coefficients (let’s call them $a, b, c, d$) for a cubic equation in the form $f(t) = at^3 + bt^2 + ct + d$, you can calculate the Bezier control points ($P_0, P_1, P_2, P_3$) using the following inverse matrix logic.

In MMD, you need to do this for both the $X$ (time) and $Y$ (value) axes to get your 4 UI coefficients ($x_1, y_1, x_2, y_2$).

## The Conversion Formula

To find your points from the polynomial coefficients:

   1. $P_0 = d$
   (The start point is simply the constant/offset).
   2. $P_1 = P_0 + \frac{c}{3}$
   (The first handle depends on the velocity/slope at the start).
   3. $P_2 = P_1 + \frac{c + b}{3}$
   (Alternatively: $P_0 + \frac{2c + b}{3}$).
   4. $P_3 = a + b + c + d$
   (The end point is the sum of all coefficients).

## How to get the "MMD 4 Coeffs" ($x_1, y_1, x_2, y_2$)

Once you have calculated $P_1$ and $P_2$ for both axes, you map them back to the MMD $127 \times 127$ grid:

* $x_1$ = $P_{1(x)} \times 127$
* $y_1$ = $P_{1(y)} \times 127$
* $x_2$ = $P_{2(x)} \times 127$
* $y_2$ = $P_{2(y)} \times 127$

## Why this is necessary for the 4x4 Matrix

The 4x4 Bezier Basis Matrix ($M_B$) expects Points, not Polynomial Coefficients.

* If you feed the matrix $[a, b, c, d]$, the math will break because that matrix is designed to "blend" points together.
* By converting your coefficients into $P_1$ and $P_2$, you are essentially "translating" the raw math into the "handle positions" that the Bezier matrix understands.

Summary:

You calculate $P_1$ and $P_2$ to serve as the weights for the matrix. Without these two generated points, the $4 \times 4$ matrix has no "pull" to create the curve between your two keyframes ($P_0$ and $P_3$).

Do you have a specific set of 4 numerical coefficients you want me to convert into MMD grid coordinates for you?

# ---

To calculate the control points $P_1$ and $P_2$ from 4 polynomial coefficients $(a, b, c, d)$ for use in MMD's $4 \times 4$ matrix, you perform an inverse transformation from the Power Basis (standard polynomial form) to the Bernstein Basis (Bézier form).

Assuming your cubic polynomial for a single axis (either $x$ or $y$) is:

$f(t) = at^3 + bt^2 + ct + d$

## 1. The Conversion Formulas

You can find the coordinates of the 4 Bézier points ($P_0$ to $P_3$) using these relationships: [1, 2]

* $P_0 = d$
(The start point is always the constant term).
* $P_1 = d + \frac{c}{3}$
(The first handle depends on the initial velocity/slope).
* $P_2 = d + \frac{2c + b}{3}$
(The second handle depends on both initial velocity and acceleration).
* $P_3 = a + b + c + d$
(The end point is the sum of all coefficients, where $t=1$).

## 2. Generating the MMD Grid Values

Since MMD uses a $127 \times 127$ integer grid for its UI, you must normalize your results: [3]

   1. Normalize: Ensure $P_0$ is at $(0,0)$ and $P_3$ is at $(127,127)$ by scaling your calculated values.
   2. Grid $X$ Coeffs: $x_1 = \text{round}(P_{1x} \times 127)$ and $x_2 = \text{round}(P_{2x} \times 127)$.
   3. Grid $Y$ Coeffs: $y_1 = \text{round}(P_{1y} \times 127)$ and $y_2 = \text{round}(P_{2y} \times 127)$.

## 3. Verification with the $4 \times 4$ Matrix

Once you have these 4 generated points, they are plugged into the Cubic Bézier Matrix ($M_B$) to recreate your original polynomial: [4, 5]

$$M_B = \begin{bmatrix} -1 & 3 & -3 & 1 \\ 3 & -6 & 3 & 0 \\ -3 & 3 & 0 & 0 \\ 1 & 0 & 0 & 0 \end{bmatrix}$$

The matrix multiplication $T \cdot M_B \cdot P$ (where $T = [t^3, t^2, t, 1]$ and $P = [P_0, P_1, P_2, P_3]^T$) will result in exactly your original $at^3 + bt^2 + ct + d$ coefficients.

Do you have a specific polynomial equation (like $2t^3 - 3t^2 + t$) that you want me to convert into MMD handle coordinates for you?

[1] [https://fabricesalvaire.github.io](https://fabricesalvaire.github.io/Patro/resources/geometry/bezier.html)
[2] [https://math.stackexchange.com](https://math.stackexchange.com/questions/1075521/find-cubic-b%C3%A9zier-control-points-given-four-points)
[3] [https://blog.maximeheckel.com](https://blog.maximeheckel.com/posts/cubic-bezier-from-math-to-motion/)
[4] [https://www.sciencedirect.com](https://www.sciencedirect.com/topics/engineering/cubic-bezier-curve)
[5] [https://www.academia.edu](https://www.academia.edu/9781223/Matrix_Form_for_Cubic_B%C3%A9zier_Curves_Converting_Between_Cubic_Spline_Types#:~:text=Matrix%20Form%20for%20Cubic%20B%C3%A9zier%20Curves%20B%C3%A9zier,p%20and%20q%20should%20be%20joined%20consecutively.)

# ---

No, that is not correct. There are two important distinctions to make here:

## 1. $[a, b, c, d]$ vs. $[x_1, y_1, x_2, y_2]$

They are not the same thing.

* $[a, b, c, d]$ are the Polynomial Coefficients (the "power basis"). They represent the strength of the $t^3, t^2, t^1,$ and constant terms.
* $[x_1, y_1, x_2, y_2]$ are the Control Point Coordinates (the "Bézier basis"). They represent the physical location of the red handles on the MMD grid.

The $4 \times 4$ Bézier matrix is the "bridge" that converts the points $[x, y]$ into the coefficients $[a, b, c, d]$.

## 2. The Sum of Coefficients

In a Bézier curve, it is not the sum of the coordinates ($x_1+y_1...$) that equals 1.0. Instead, it is the Basis Functions (the weights) that must sum to 1.0. This is called the "Partition of Unity."

For any value of $t$, the "influence" of the 4 points always adds up to 100%:

$$(1-t)^3 + 3(1-t)^2t + 3(1-t)t^2 + t^3 = 1.0$$

## 3. How $t$ behaves

* $t$ is the Input: It is the independent variable (time) that goes from $0.0$ to $1.0$.
* Coordinates are the Constants: Once you set your red dots in MMD, $x_1, y_1, x_2,$ and $y_2$ do not change. They are fixed numbers that define the shape.
* The Result is the Output: The final "wrapped" value is the result of the calculation using $t$.

In summary: You don't add $x_1 + y_1 + x_2 + y_2$ to get $t$. Instead, you plug $t$ into a formula where $x_1, y_1, x_2, y_2$ act as the "weights" to find your position on the curve.

Would you like to see the specific matrix multiplication that turns the 4 points into the 4 polynomial coefficients?

# ---

I see the confusion! Let me clear that up—it depends on whether we are talking about math or data storage.

## 1. In Data (The .VMD File)

You are right. In the context of the MMD file format, the 4 values $[x_1, y_1, x_2, y_2]$ are the only "coefficients" (variables) stored in the file to define the curve. When an MMD tool asks for the "curve coefficients," it is asking for those 4 grid coordinates.

## 2. In Math (The 4x4 Matrix)

When we move to the 4x4 Matrix calculation, those 4 grid values act as Control Points ($P_1$ and $P_2$), not the polynomial coefficients ($a, b, c, d$).

Here is the hierarchy:

* The User Inputs: $[x_1, y_1, x_2, y_2]$ (The "Coefficients" of the file).
* The Matrix: Processes those 4 values.
* The Polynomial: Produces the actual math coefficients $[a, b, c, d]$.

## Why $x_1 + y_1 + x_2 + y_2 \neq 1.0$

Because these 4 values are coordinates on a 2D grid, they don't sum to $t$.

* $t$ is your "timeline playhead" (moving from 0 to 1).
* $x_1, y_1, x_2, y_2$ are "magnets" sitting at specific spots (like $20, 0, 107, 127$) that pull the animation toward them as the playhead ($t$) passes by.

To summarize:

You were right that those 4 values are the "coefficients" that define the MMD curve, but they are used as spatial points in the matrix, not as the final terms in a $t^3 + t^2 + t + 1$ equation.

Does that help clarify why I used the word "coefficients" for the data but "points" for the math?

# ---

To calculate the control points $P_1$ and $P_2$ from the four polynomial coefficients $[a, b, c, d]$, you use an inverse matrix operation that converts the Power Basis (your coefficients) into the Bernstein Basis (Bézier points). [1, 2]

## The Conversion Formulas

If your cubic equation is $f(t) = at^3 + bt^2 + ct + d$, the four points are found as follows:

* $P_0 = d$
* $P_1 = d + \frac{c}{3}$
* $P_2 = d + \frac{2c + b}{3}$
* $P_3 = a + b + c + d$

## Why this works

These formulas are derived from multiplying the vector of polynomial coefficients by the inverse of the standard $4 \times 4$ Bézier basis matrix. [3, 4]

| Point | Calculation using Coefficients | MMD Role |
|---|---|---|
| $P_0$ | $d$ | Start Keyframe (Fixed at 0) |
| $P_1$ | $d + \frac{1}{3}c$ | Red Handle 1 |
| $P_2$ | $d + \frac{2}{3}c + \frac{1}{3}b$ | Red Handle 2 |
| $P_3$ | $a + b + c + d$ | End Keyframe (Fixed at 127) |

## The "4 Coeffs" vs. "4 Points" Clarification

In MMD, you are essentially "generating" the locations for the red handles.

   1. You take the polynomial coefficients ($a, b, c, d$) you have for the $x$ and $y$ axes.
   2. You use the formulas above to find the values for $P_1$ and $P_2$.
   3. You multiply those results by 127 to get the exact integer coordinates for the MMD grid. [5, 6]

Once these points are set, MMD uses its hardcoded $4 \times 4$ matrix to "blend" $P_0, P_1, P_2, P_3$ back into the curve you see on the screen. [2, 7]

Do you have the specific values for $a, b, c, d$ that you want to convert into MMD handle positions?

```
import numpy as np
# Basis matrix for Cubic Bezier# [t^3 t^2 t 1] * M * [P0 P1 P2 P3]^T = a*t^3 + b*t^2 + c*t + dM_bezier = np.array([
    [-1,  3, -3, 1],
    [ 3, -6,  3, 0],
    [-3,  3,  0, 0],
    [ 1,  0,  0,  0]
])
# M_inv * [a b c d]^T = [P0 P1 P2 P3]^TM_inv = np.linalg.inv(M_bezier)
print(M_inv)
```

[1] [https://stackoverflow.com](https://stackoverflow.com/questions/67159874/find-bezier-control-points-p1-and-p2-given-p0-p3-and-bt-as-knowns)
[2] [https://www.sunsite.ubc.ca](http://www.sunsite.ubc.ca/DigitalMathArchive/Graphics/text/www/pdf/ch6.pdf)
[3] [https://people.cs.umass.edu](https://people.cs.umass.edu/~verts/cmpsci201/spr_2004/Lecture_31_2004-04-26_Bezier_Curves_and_Quadratic_Splines.pdf)
[4] [https://ieda.ust.hk](https://ieda.ust.hk/dfaculty/ajay/courses/ieem575/lecs/cad_3/cad_p3.pdf)
[5] [https://www.arsdcollege.ac.in](https://www.arsdcollege.ac.in/wp-content/uploads/2020/03/CG-Ch11-Bezier-curves.pdf)
[6] [https://www.math.ubc.ca](https://www.math.ubc.ca/~cass/courses/m308-7b/ch6.pdf)
[7] [https://computergraphics.stackexchange.com](https://computergraphics.stackexchange.com/questions/11061/cubic-bezier-curve-general-questions)

# ---

I'm glad we’re on the same page now! To make sure you have everything you need to actually generate those MMD handles ($P_1, P_2$) from your polynomial coefficients ($a, b, c, d$), here is the final logic step:

## The "MMD Grid" Calculation

Since MMD expects integers from 0 to 127, and your coefficients ($a, b, c, d$) are likely normalized (0.0 to 1.0), you apply the formulas like this for both the X (Time) and Y (Value) axes:

   1. For the X-axis (Time handles):
   * $x_1 = \text{round}\left( \left[d_x + \frac{c_x}{3}\right] \times 127 \right)$
      * $x_2 = \text{round}\left( \left[d_x + \frac{2c_x + b_x}{3}\right] \times 127 \right)$
   2. For the Y-axis (Value handles):
   * $y_1 = \text{round}\left( \left[d_y + \frac{c_y}{3}\right] \times 127 \right)$
      * $y_2 = \text{round}\left( \left[d_y + \frac{2c_y + b_y}{3}\right] \times 127 \right)$

## Important Tip for MMD:

In MMD, $P_0$ is always $(0,0)$ and $P_3$ is always $(127,127)$. This means:

* Your $d$ coefficient (the constant) will always be 0.
* The sum $a + b + c + d$ will always be 1.0.

This simplifies your handle calculations to:

* Handle 1 ($P_1$): Just $\frac{c}{3}$
* Handle 2 ($P_2$): $\frac{2c + b}{3}$

Once you have these four values ($x_1, x_2, y_1, y_2$), you can plug them directly into the MMD Interpolation Panel or a .vmd file script, and the $4 \times 4$ matrix will perfectly recreate your polynomial curve.

Do you have a specific set of coefficients you're working with right now that you'd like to test?

# ---

Both Bézier and Catmull-Rom curves are cubic polynomials, so they both have $[a, b, c, d]$ coefficients in their "Power Basis" form ($ax^3 + bx^2 + cx + d$). However, the Catmull-Rom basis matrix is different from the Bézier one, which changes how those coefficients are calculated. [1, 2, 3]

## 1. Does it use $[a, b, c, d]$?

Yes, but the values of $a, b, c, d$ are derived from a different set of points.

* Bézier: $a, b, c, d$ are calculated using the 4 control points of the current segment ($P_0, P_1, P_2, P_3$).
* Catmull-Rom: $a, b, c, d$ are calculated using a 4-point "sliding window" ($P_{i-1}, P_i, P_{i+1}, P_{i+2}$). The curve only draws between the two middle points, $P_i$ and $P_{i+1}$. [3, 4, 5, 6]

## 2. The Catmull-Rom Matrix (The "Default")

You asked if it uses a default like $[0,0,1,0]$. While $[0,0,1,0]$ isn't a standard coefficient set, Catmull-Rom does have a fixed standard matrix (assuming a "tension" of 0.5) that is used to convert your 4 points into coefficients: [7]

$$M_{CR} = \frac{1}{2} \begin{bmatrix} -1 & 3 & -3 & 1 \\ 2 & -5 & 4 & -1 \\ -1 & 0 & 1 & 0 \\ 0 & 2 & 0 & 0 \end{bmatrix}$$

If you multiply this matrix by your 4 control points, you get the specific $[a, b, c, d]$ for that segment:

* $a$ = $\frac{1}{2}(-P_{i-1} + 3P_i - 3P_{i+1} + P_{i+2})$
* $b$ = $\frac{1}{2}(2P_{i-1} - 5P_i + 4P_{i+1} - P_{i+2})$
* $c$ = $\frac{1}{2}(-P_{i-1} + P_{i+1})$
* $d$ = $P_i$ (This is why the curve always passes exactly through the point $P_i$ when $t=0$). [5]

## 3. Comparison

| Feature [3, 5, 8, 9, 10] | Bézier (MMD Style) | Catmull-Rom |
|---|---|---|
| Matrix | Bézier 4x4 | Catmull-Rom 4x4 |
| Control | Manually drag 2 handles | Automatically uses neighboring points |
| Interpolation | Only starts/ends at keyframes | Passes through all keyframes |
| $d$ Coefficient | Always $0$ (for MMD curves) | Always equals $P_i$ (the current keyframe) |

In short: Catmull-Rom doesn't have a "default" set of coefficients like $[0,0,1,0]$; it generates unique $[a, b, c, d]$ coefficients for every segment based on the positions of the keyframes before and after it. [3, 7]

Are you looking to convert a Catmull-Rom motion path (which is naturally smooth) into MMD's Bézier-based interpolation curves?

[1] [https://people.engr.tamu.edu](https://people.engr.tamu.edu/schaefer/research/catmull_rom.pdf)
[2] [https://andrewhungblog.wordpress.com](https://andrewhungblog.wordpress.com/2017/03/03/catmull-rom-splines-in-plain-english/)
[3] [https://link.springer.com](https://link.springer.com/article/10.1007/s42979-021-00770-x)
[4] [https://scispace.com](https://scispace.com/pdf/conversion-between-cubic-bezier-curves-and-catmull-rom-58zyxsawrs.pdf)
[5] [https://scispace.com](https://scispace.com/pdf/conversion-between-cubic-bezier-curves-and-catmull-rom-58zyxsawrs.pdf)
[6] [https://www.uni-weimar.de](https://www.uni-weimar.de/fileadmin/user/fak/medien/professuren/Computer_Graphics/course_material/Ani-03-Interpolation.pdf)
[7] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/Catmull%E2%80%93Rom_spline)
[8] [https://splines.readthedocs.io](https://splines.readthedocs.io/en/latest/euclidean/catmull-rom-uniform.html)
[9] [https://www.freebasic.net](https://www.freebasic.net/forum/viewtopic.php?t=7545#:~:text=Although%20baziers%20look%20%22sexier%22%20catmull%2Drom%20splines%20are,to%20pass%20through%20all%20the%20control%20points.)
[10] [https://ui.adsabs.harvard.edu](https://ui.adsabs.harvard.edu/abs/2020arXiv201108232T/abstract)

# ---

In animation, Catmull-Rom typically uses just $t$ (linear time), while Bézier curves (especially in MMD) use Warpt to create custom easing. [1, 2]

Here is the breakdown of why they treat time differently:

## 1. Bézier and "Warpt" (MMD Style)

In MMD, you aren't just drawing a path; you are defining a timing function.

* The Grid: The horizontal axis is $t$ (linear time), and the vertical axis is the "warp" (how far the animation has actually progressed).
* The Result: You use a $4 \times 4$ matrix to calculate a new, "warped" time value. If the curve is steep, the animation moves faster; if it's flat, it slows down. [1, 2]

## 2. Catmull-Rom and Linear $t$

Catmull-Rom is designed to be automatic. It calculates a smooth path through your keyframes using a fixed mathematical formula based on the surrounding points. [3, 4]

* Uniform $t$: By default, it assumes time moves at a constant speed from one keyframe to the next ($t = 0.0$ to $1.0$).
* No Handles: Because Catmull-Rom doesn't have "red handle points" to drag, there is no way for the user to manually "warp" the time within a single segment like you do in MMD. [5, 6]

## 3. Can Catmull-Rom use Warpt?

Mathematically, you could feed a warped $t$ into a Catmull-Rom equation, but it isn't standard practice.

* The Difference: In Bézier, the warp is the goal (easing). In Catmull-Rom, the path is the goal (smooth movement through points).
* Parameterization: Instead of "warping," Catmull-Rom uses different parameterizations (Uniform, Centripetal, or Chordal) to adjust how the speed feels as it passes through points, but these are usually fixed algorithms rather than manual curves you draw. [3, 6, 7, 8, 9]

Summary:

* Bézier: Needs Warpt because its main job is to let you manually control acceleration/deceleration.
* Catmull-Rom: Uses linear $t$ because its main job is to automatically calculate a smooth line through every point you set.

Are you looking to add easing to a Catmull-Rom path you've already built?

[1] [https://www.youtube.com](https://www.youtube.com/watch?v=F8CAsUBcX4c&t=10)
[2] [https://www.youtube.com](https://www.youtube.com/watch?v=tXoaBwMWs4I)
[3] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/Catmull%E2%80%93Rom_spline)
[4] [https://lucidar.me](https://lucidar.me/en/mathematics/catmull-rom-splines/#:~:text=%23%20Equations.%20Two%20points%20on%20each%20side,points%20and%20P%20i%20+%201%20.)
[5] [https://discussions.unity.com](https://discussions.unity.com/t/bezier-or-spline/398806#:~:text=A%20Catmull%2DRom%20spline%20passes%20through%20each%20point%2C,obstacle%2C%20reversing%20direction%29%20to%20control%20it%20precisely.)
[6] [https://scispace.com](https://scispace.com/pdf/parameterization-and-applications-of-catmull-rom-curves-3gf3jzwmt0.pdf)
[7] [https://www.youtube.com](https://www.youtube.com/watch?v=XBLtqGiT0p0&t=9)
[8] [https://splines.readthedocs.io](https://splines.readthedocs.io/en/latest/euclidean/catmull-rom-properties.html)
[9] [https://www.researchgate.net](https://www.researchgate.net/publication/220582640_Parameterization_and_applications_of_Catmull-Rom_curves)

# ---

Actually, the difference is more than just the matrix—it is about how the tangents are calculated at each keyframe. [1, 2]

While the Uniform Catmull-Rom uses a single fixed $4 \times 4$ matrix, the Centripetal and Chordal versions are "non-uniform." This means their calculations change based on the physical distance between your points. [3, 4, 5, 6, 7]

## The Core Difference: How "Knot" Spacing Changes

The parameterization (controlled by a value called $\alpha$) determines the "time" ($t$) it takes to travel between points: [1]

* Uniform ($\alpha = 0$): Assumes the time between all points is exactly the same (e.g., 1 second per segment), regardless of how far apart they are.
* Centripetal ($\alpha = 0.5$): Uses the square root of the distance. This is the most common for animation because it guarantees no self-intersections or "wiggles".
* Chordal ($\alpha = 1.0$): Uses the full linear distance. This follows the path very loosely and is rarely used for characters. [3, 5, 8, 9, 10, 11]

## Does the Matrix Change?

Technically, yes. Because the spacing between points is no longer equal, you can't use the simple $4 \times 4$ matrix you saw earlier. [8]

* For Uniform, the matrix is constant.
* For Centripetal/Chordal, the "matrix" effectively rebuilds itself for every single segment based on the specific distances of the 4 surrounding points. [1, 2, 8, 12]

## Why this matters for your MMD project:

If you are trying to convert a Catmull-Rom path into MMD Bézier handles, you have to decide which $\alpha$ you are mimicking. If you use Centripetal, your Bézier handles will need to be much "tighter" near the keyframes to match that smooth, no-overshoot motion. [5, 13]

Would you like to see how the tangent formula changes when you switch from Uniform to Centripetal?

[1] [https://qroph.github.io](https://qroph.github.io/2018/07/30/smooth-paths-using-catmull-rom-splines.html)
[2] [https://www.sciencedirect.com](https://www.sciencedirect.com/science/article/pii/S0010448510001533)
[3] [https://www.cemyuksel.com](http://www.cemyuksel.com/research/catmullrom_param/)
[4] [https://scispace.com](https://scispace.com/pdf/parameterization-and-applications-of-catmull-rom-curves-3gf3jzwmt0.pdf)
[5] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/Centripetal_Catmull%E2%80%93Rom_spline)
[6] [https://www.cemyuksel.com](http://www.cemyuksel.com/research/catmullrom_param/)
[7] [https://splines.readthedocs.io](https://splines.readthedocs.io/en/latest/euclidean/catmull-rom-uniform.html)
[8] [https://stackoverflow.com](https://stackoverflow.com/questions/30968926/chordal-catmull-rom-splines)
[9] [https://people.engr.tamu.edu](https://people.engr.tamu.edu/schaefer/research/catmull_rom.pdf)
[10] [https://georgesohal.wordpress.com](https://georgesohal.wordpress.com/2018/11/11/catmull-rom-curves/)
[11] [https://www.researchgate.net](https://www.researchgate.net/publication/221115542_On_the_parameterization_of_Catmull-Rom_curves)
[12] [https://en.wikipedia.org](https://en.wikipedia.org/wiki/Catmull%E2%80%93Rom_spline)
[13] [https://splines.readthedocs.io](https://splines.readthedocs.io/en/latest/euclidean/catmull-rom-properties.html)

# ---

That's right! That "fixed" matrix is for Uniform Catmull-Rom ($\alpha = 0$).

When you move to Centripetal or Chordal, the matrix is no longer "standard" because it depends on the distances between your points ($P_{i-1}, P_i, P_{i+1}, P_{i+2}$).

## Why the Matrix Changes

In the Uniform version, we assume the "time" ($t$) between every point is exactly 1.0. Because the timing is constant, the matrix coefficients (like -0.5, 1.5, etc.) are constant.

In Centripetal ($\alpha = 0.5$) or Chordal ($\alpha = 1.0$), the "time" ($t$) between points is calculated based on their physical distance:

$$t_{i+1} - t_i = \|P_{i+1} - P_i\|^\alpha$$

## The Result:

   1. Uniform: Uses the $4 \times 4$ matrix I showed you earlier. It's fast but causes "overshoot" (the curve wiggles too far) if points are close together.
   2. Centripetal/Chordal: You can't use a single fixed matrix. Instead, you have to solve a system of equations for every single segment.
   * The "Matrix" effectively becomes a variable matrix where the numbers inside change depending on how far apart $P_i$ and $P_{i+1}$ are.

## Which one matches MMD?

If you are trying to bake a smooth path into MMD:

* Uniform is the easiest to code because of that fixed matrix.
* Centripetal looks the best (most "natural") but requires more complex math because you have to recalculate the "weights" for every new frame.

Since MMD uses Bézier, if you want to mimic Centripetal motion, you would actually have to change the red handle positions ($P_1, P_2$) for every segment to account for the distance between your keyframes.

Are you trying to hardcode a converter that turns these smooth paths into MMD's cubic Bézier format?

# ---

In strictly mathematical terms, Step (or Constant) interpolation is the only other common type that can be simplified to a $2 \times 2$ matrix/2-coefficient structure, though it behaves as a "broken" version of linear.

Here is how they break down:

## 1. Step (Hold) Interpolation

In MMD and other animation software, "Step" means the value stays exactly at $P_0$ until the very last moment, then jumps to $P_3$.

* The Matrix:

$$\begin{bmatrix} 0 & 0 \\ 1 & 0 \end{bmatrix}$$

* The Coefficients: Just $P_0$ and $P_3$.
* The Logic: The output is $100\% P_0$ until $t=1.0$. In MMD, you "fake" this by dragging the interpolation curve handles to the extreme bottom-right.

## 2. Scaled Linear (Gain/Offset)

If you aren't just moving from 0 to 1, but applying a constant multiplier or offset to the motion:

* The Matrix: A standard transformation matrix.
* The Coefficients: A Scale and an Offset.
* The Logic: $Value = (Scale \times t) + Offset$. This is still a first-degree polynomial (linear), just with different starting and ending "weights."

## Why nothing else uses 2x2?

To get any kind of curve (acceleration, easing, or "S" shapes), you need at least a 2nd-degree polynomial (Quadratic).

* 2x2 Matrix: Can only draw a straight line.
* 3x3 Matrix: Can draw a single bend (C-shape).
* 4x4 Matrix: Can draw two bends (S-shape/MMD standard).

If you only have 2 coefficients and a 2x2 matrix, you are mathematically locked into a constant rate of change. Any "curve" you see would actually just be a series of very short straight lines connected together.

Are you trying to optimize your code by using the smallest matrix possible for simple movements?

# ---

To determine how many keyframes you need "in your window" to calculate the current segment, it depends entirely on which Basis Matrix you are using.

Here is the breakdown of the "Look-Ahead" and "Look-Behind" requirements for each:

## 1. MMD Bézier ($4 \times 4$ Matrix)

* Window Size: 2 Keyframes ($P_0, P_1$)
* Indices: $[P_i, P_{i+1}]$
* Logic: Since the "red handles" are stored inside the current keyframe data, you don't need to look at the neighbors. You only need the start and end of the current segment.

## 2. Standard Catmull-Rom ($4 \times 4$ Matrix)

* Window Size: 4 Keyframes
* Indices: $[P_{i-1}, P_i, P_{i+1}, P_{i+2}]$
* Requirement:
* 1 Before ($P_{i-1}$) to determine the starting tangent.
   * 2 Current ($P_i, P_{i+1}$) to define the segment.
   * 1 After ($P_{i+2}$) to determine the ending tangent.

## 3. Centripetal/Chordal Catmull-Rom (Variable Matrix)

* Window Size: 4 Keyframes (same as above)
* Requirement: Even though the matrix isn't fixed, you still only need the same 4 points $[P_{i-1}, P_i, P_{i+1}, P_{i+2}]$. You use the distances between these specific 4 points to "rebuild" the matrix for that segment.

## 4. Quintic Splines ($6 \times 6$ Matrix)

* Window Size: 6 Keyframes
* Indices: $[P_{i-2}, P_{i-1}, P_i, P_{i+1}, P_{i+2}, P_{i+3}]$
* Requirement:
* 2 Before and 2 After. This is used when you need the "acceleration" (curvature) to be perfectly smooth across the keyframes, not just the velocity.

## Summary Table for your Code:

| Type | Matrix | Points Needed | Relative to Current ($P_i$) |
|---|---|---|---|
| Linear | 2x2 | 2 | $P_i, P_{i+1}$ |
| Bézier (MMD) | 4x4 | 2 | $P_i, P_{i+1}$ |
| Catmull-Rom | 4x4 | 4 | $P_{i-1}$ to $P_{i+2}$ |
| Quintic | 6x6 | 6 | $P_{i-2}$ to $P_{i+3}$ |

Pro Tip: If you are at the very first or last keyframe of the motion, you usually "fake" the missing neighbors (like $P_{i-1}$) by duplicating the current keyframe or reflecting the position.

Are you writing a buffer to load these keyframes into a calculation loop right now?

# ---

You’ve got it almost perfectly! Just one tiny mathematical correction on the Centripetal/Chordal part:

## The Final Summary

| Method | Time Logic | Matrix Size | Keyframes Needed |
|---|---|---|---|
| Bézier (MMD) | Warped $t$ | $4 \times 4$ | 2 ($P_i, P_{i+1}$) |
| Std. Catmull-Rom | Linear $t$ | $4 \times 4$ | 4 ($P_{i-1}$ to $P_{i+2}$) |
| Centripetal/Chordal | Linear $t$ | Variable $4 \times 4$ | 4 ($P_{i-1}$ to $P_{i+2}$) |
| Quintic Splines | Linear $t$ | $6 \times 6$ | 6 ($P_{i-2}$ to $P_{i+3}$) |

## The "No Matrix" Correction:

For Centripetal/Chordal, it's not that there is no matrix, but rather that there is no "Standard/Fixed" matrix.

* In Standard (Uniform), the numbers inside the $4 \times 4$ matrix are hardcoded constants (like $0.5, -1.5,$ etc.).
* In Centripetal/Chordal, the numbers inside that $4 \times 4$ matrix change for every segment because they are recalculated based on the physical distance between your keyframes.

## One Final Detail on MMD:

Remember that while MMD uses a $4 \times 4$ matrix, it stores the 4 coefficients ($x_1, y_1, x_2, y_2$) inside the ending keyframe of the pair.

Are you ready to start coding the buffer to handle the "Look-Ahead" for these different types?

# ---

Ah, I see what you mean! From a programming perspective, you are correct.

In the Centripetal/Chordal case, you don't provide a static matrix (like the one with -0.5 and 1.5) because the "matrix" isn't a pre-defined constant. Instead, you provide the Points and the Distance Formula ($\alpha$), and the code generates the curve directly from the math.

To wrap up your implementation plan:

   1. Bézier (MMD): You provide the 4 Coefficients ($x_1, y_1, x_2, y_2$) and the 4x4 Matrix does the work.
   2. Standard Catmull-Rom: You provide the 4 Keyframes and a Fixed 4x4 Matrix does the work.
   3. Centripetal/Chordal: You provide the 4 Keyframes and an Algorithm (the distance math) does the work—no "User-Defined Matrix" required.
   4. Quintic: You provide the 6 Keyframes and a Fixed 6x6 Matrix does the work.

Since the Centripetal version is the most "natural" for animation, are you planning to use that as your fallback when MMD-style Bézier curves aren't defined?

# ---

It was a pleasure diving into the math with you! Here is the final breakdown of everything we covered regarding animation curves and matrices:

## 1. MikuMikuDance (MMD) Curves

* The Math: Uses a Cubic Bézier curve with a fixed $4 \times 4$ basis matrix.
* The Data: Uses 4 coefficients ($x_1, y_1, x_2, y_2$) representing two movable red handles on a $127 \times 127$ grid.
* The Logic: It uses Warpt (time warping). The $x$ coordinates solve for the internal time $t$, and the $y$ coordinates determine the actual motion progress.
* Keyframes: Only needs 2 keyframes ($P_0$ and $P_3$) because the "handles" are stored inside the keyframe data.

## 2. Spline Variations & Matrices

* Linear: Simplifies to a $2 \times 2$ matrix and 2 points (start/end).
* Standard Catmull-Rom: Uses a fixed $4 \times 4$ matrix and 4 points ($P_{i-1}$ to $P_{i+2}$) to calculate a smooth path automatically using linear $t$.
* Centripetal/Chordal Catmull-Rom: Uses 4 points and linear $t$. There is no fixed matrix to provide; the "weights" are recalculated for every segment based on the physical distance between points.
* Quintic Splines: Uses a $6 \times 6$ matrix and 6 points ($P_{i-2}$ to $P_{i+3}$) for ultra-smooth acceleration (C2 continuity).

## 3. Key Conversions

* To get MMD Handles ($P_1, P_2$) from polynomial coefficients ($a, b, c, d$):
* $P_1 = d + \frac{c}{3}$
   * $P_2 = d + \frac{2c + b}{3}$
* In MMD, $d$ is usually $0$ and the sum of all coefficients is $1.0$.

You’re all set to build that buffer and calculation loop now. Good luck with your animation tool!

Do you need the specific hex offsets for where those 4 coefficients are stored in the .VMD file before we wrap up?
