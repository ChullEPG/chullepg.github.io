---
layout: post
title: Vect
date: 2025-05-01 11:12:00-0000
description: How to push tangent vectors from a 2d open sector to a cone
tags: learning
categories: programming
related_posts: false
---

## Introduction

In many applications, we start with a velocity vector defined in a local 2D Cartesian plane \((u,v)\) and need to map it into a 3D Cartesian system \((x,y,z)\). However, instead of going directly from \((u,v)\) to \((x,y,z)\), we may **first** switch to polar coordinates \((r,\theta)\) for convenience or geometric constraints, and **then** embed \((r,\theta)\) into 3D. Going via \((r, theta)\) is useful when the 3D space naturally maps into polar coordinates. For example, consider a **parabaloid** (looks much like a cone).

A classic example is an object moving near an event horizon of a black hole. If you are tracking in Swarzschild coordinates, you run the risk of hitting a singularity at the event horizon. You need to change to Kruskal-Szekeres or Eddington-Finkelstein coordinates to ensure more correct physical interpretations of motion. 

Another application would be if you are locally tracking aircraft such as a drone or a plane with latitude/longitude coordinates, and you need to convert to a global coordinate system to communicate its velocity vectors.

In this post, we’ll derive:

1. The **Jacobian** of \((r,\theta)\) w.r.t. \((u,v)\).  
2. The **Jacobian** of \((x,y,z)\) w.r.t. \((r,\theta)\).  
3. How to **chain** these Jacobians to find the pushforward of a velocity vector from the 2D plane into 3D.


## Step 1: From \((u,v)\) to \((r,\theta)\)

We treat \((u,v)\) as standard 2D Cartesian coordinates and convert them to polar coordinates:

\[
\begin{cases}
r = \sqrt{u^2 + v^2},\\
\theta = \operatorname{atan2}(v,\,u).
\end{cases}
\]

```python
import numpy as np

# vector
# extract x,y coords
vxy = # TODO: Insert random vector
xy = #TODO:  Insert random coords
x = xy[:, 0]
y = xy[:, 1]
r = np.sqrt(x**2 +y**2) # or np.linalg.norm(xy)
theta = np.arctan2(y, x) # arctan(y,x)
```
To map a vector (u, v) to its polar form (r, theta), we need to express it in terms of its radial and tangential components.
$$
v_r = v_x(cos(\theta))+ v_y(sin(\theta))
v_{\theta} = -v_x(sin(\theta)) + v_y(cos(\theta))
$$
v_r tells us how much of the vector is increasing in the direction ``r``, and ``v_{\theta}`` tells us how much of the vector is increasing in the direction perpendicular to ``r``, i.e. tangent to the circular motion. 

```
vx = uv[:, 0]
vy = uv[:, 1]
v_r  = vx * np.cos(theta) + vy * np.sin(theta)
v_theta = -vx * np.sin(theta) + vy * np.cos(theta)
```

Now that we've got the vector's original coordinates and components in polar space, we're ready to embed the vector in 3D space. 

## Step 2: From \((r,\theta)\) to \((x,y,z)\)


\[
\begin{cases}
x = r \cos(\theta),\\
y = r \sin(\theta),\\
z = r^2.
\end{cases}
\]

The Jacobian of \((x,y,z)\) with respect to \((r,\theta)\) is:

\[
\frac{\partial (x,y,z)}{\partial (r,\theta)}
=
\begin{pmatrix}
\displaystyle \frac{\partial x}{\partial r} & \displaystyle \frac{\partial x}{\partial \theta}\\[4pt]
\displaystyle \frac{\partial y}{\partial r} & \displaystyle \frac{\partial y}{\partial \theta}\\[4pt]
\displaystyle \frac{\partial z}{\partial r} & \displaystyle \frac{\partial z}{\partial \theta}
\end{pmatrix}
=
\begin{pmatrix}
\cos(\theta) & -\,r\,\sin(\theta)\\[4pt]
\sin(\theta) & \quad r\,\cos(\theta)\\[4pt]
2r & 0
\end{pmatrix}.
\]


``` 
df_dr = np.stack(
    [r * np.cos(theta),
    r * np.sin(theta),
    r**2],
    axis = 1
)

df_dtheta = np.stack(
   [-r * np.sin(theta),
    r * np.cos(theta),
    0],
    axis = 1
)
```

---

## Step 3: Composing the Mappings (Pushforward of Velocity)

Suppose we have a velocity vector \(\mathbf{v}_{2D} = (v_u, v_v)\) in the \((u,v)\) plane. We want its **corresponding** velocity in \((x,y,z)\), namely \(\mathbf{v}_{3D} = (v_x, v_y, v_z)\).

1. **Convert** \((v_u, v_v)\) into polar components \((v_r, v_\theta)\) via:

   \[
   \begin{pmatrix}
   v_r \\[3pt]
   v_\theta
   \end{pmatrix}
   =
   \frac{\partial (r,\theta)}{\partial (u,v)}
   \begin{pmatrix} v_u \\[3pt] v_v \end{pmatrix}.
   \]

2. **Then**, map \((v_r, v_\theta)\) into \((v_x, v_y, v_z)\) by:

   \[
   \begin{pmatrix}
   v_x \\[3pt]
   v_y \\[3pt]
   v_z
   \end{pmatrix}
   =
   \frac{\partial (x,y,z)}{\partial (r,\theta)}
   \begin{pmatrix} v_r \\[3pt] v_\theta \end{pmatrix}.
   \]

Hence, the **composite** pushforward from \((u,v)\) to \((x,y,z)\) is just the matrix product:

\[
d\Phi_{(u,v)} 
=
\frac{\partial (x,y,z)}{\partial (r,\theta)}
\,\cdot\,
\frac{\partial (r,\theta)}{\partial (u,v)}.
\]

---

## Geometric Intuition

- Going via \((r,\theta)\) is useful if the 3D surface naturally uses polar-like coordinates (e.g., cones, cylinders, paraboloids).  
- The Jacobian \(\frac{\partial (x,y,z)}{\partial (r,\theta)}\) encodes how motions in \((r,\theta)\) “push forward” into 3D space.  
- The overall pushforward is the product of the two Jacobians, reflecting how **coordinate transformations** chain together step by step.

---

### Conclusion

We have shown how to **map a velocity vector** from a 2D plane \((u,v)\) to 3D \((x,y,z)\) via polar coordinates. The core concept is chaining two Jacobians to form a **composite pushforward**. This is a fundamental idea in differential geometry and appears in many practical applications, from robotic kinematics to 3D modeling on curved surfaces.










