---
layout: post
title: Ray Poll and Kill
date: 2025-01-20 11:12:00-0000
description: How to kill individual processes with Ray. 
tags: code
categories: programming
related_posts: false
---

Ray poll and kill allows a user to detect and terminate unresponsive task processes in a distributed environment without cancelling the entire script altogether. This is especially helpful in scenarios where some of your tasks might hang, crash, or get stuck in an infinite loop, potentially locking up all your workers and grinding your entire job to a halt.

The essence of the approach is:

1. Poll the set of running tasks at regular intervals (using ray.wait() with a short timeout).
2. Identify any tasks that have exceeded a maximum expected run time.
3. Cancel the stuck tasks one-by-one with ray.cancel(task_ref, force=True) instead of cancelling the entire job or waiting forever.

Below is a reference script, followed by a more detailed motivation and explanation:

```python
import ray
import time 
import multiprocessing


# Polling timeout (after which a task is considered 'stuck')
TIMEOUT = 100 

num_cpus = multiprocessing.cpu_count() // 2 
ray.init(num_cpus = num_cpus, ignore_reinit_error = True)


# Example ray task that sometimes hangs
@ray.remote
def maybe_hang(idx: int) -> str: 
    """ A task that randomly sleeps or gets stuck."""
    import random 
    # 20% chance of infinite loop
    if random.random() < 0.20: 
        while True:
            pass
    sleep_time = random.uniform(1,5)
    time.sleep(sleep_time)
    return f"Task {idx} completed in {sleep_time:.2f}s"

# Create references to tasks
task_refs = [maybe_hang.remote(i) for i in range(10)]

# Track when each task started
for ref in task_refs:
    start_times[ref] = time.time() 


not_done = set(task_refs)

# Main polling loop: 
while not_done:  
    done, not_done = ray.wait(task_refs, num_returns = 1, timeout = 1.0)

    # If any tasks have completed, remove them from not_done 
    for task_ref in done:
        # Attempt to retrieve the result
        try: 
            ray.get(task_ref)
        except Exception as e:
            print(f"Task {ref} failed with: {e}")
        not_done.remove(task_ref)


    # Poll unfinished tasks
    for task_ref in not_done:
        elapsed_time = time.time() - start_times[ref]
        if elapsed_time > TIMEOUT: 
            to_cancel.append(task_ref)

    # Cancel tasks that exceeded TIMEOUT 
    for task_ref in to_cancel:
        print(f"Cancelling task {ref}") 
        ray.cancel(task_ref)
        futures.remove(task_ref)

ray.shutdown()
```

#### How it works

**Motivation:**

If workers get stuck and cannot free themselves, your script eventually runs out of free workers. Over time, tasks line up, and the script slows down -- or grinds to a halt. The built-in solution, ``ray.wait()``, will let you see which tasks are "done", but it does not necessarily kill unresponsive tasks that ignore polite termination signals. 

**SIGTERM vs SIGKILL** 

* SIGTERM: Polite. It knocks on the door of a process and waits for it to acknowledge SIGTERMs presence. However, this can be too polite. If your code is in an infinite loop or a blocking system call, it might never get around to acknowledging SIGTERM, so you end up with a zombie worker.  You will find these packages often lack built-in safeguards for interruption, i.e. they don't stop to check for termination signals. 
* SIGKILL: Aggressive. SIGTERM does not knock -- it busts through the wall and terminates the process immediately, no chance for cleanup or final goodbyes. Some tasks might need ``SIGKILL`` if they are truly stuck in native C/C++ or certain system calls that ignore Python's interrupt signals. 

```ray.cancel(ref, force=True)``` uses the more forceful approach. ensuring that if a subprocess is ignoring polite signals, it still gets reclaimed.


**_Tip_:** _If you are running a long loop and not sure if processes are hanging or not, you can check if your system resources are being taken up by zombie processes using commands like```top``` or ```htop```._

For me, the poll and kill strategy became useful when I distributed a task that runs [Gmsh](https://gmsh.info) under the hood. Gmsh is written in C++ and crashed out in ~5% of tasks that I was running. Initially when running the script, I noticed that over time my script would slow down, eventually grinding to a halt when all of the workers had taken up one by one.


Ray’s poll-and-kill technique provides fine-grained control over long-running or potentially stuck tasks, letting you forcibly remove outliers without taking down the entire job. By combining ```ray.wait()``` with an appropriate timeout and invoking ```ray.cancel(ref, force=True)```, you can keep your Ray clusters running smoothly—even under the unpredictable conditions often encountered in distributed computing.





