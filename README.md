# ⛩ KAMI-LEGACY

**神々の記憶を翻訳する場所**

https://kami-legacy.com

---

## What is this?

Japan has over 80,000 shrines. Each one has a *go-yuisho* (御由緒) — a written record of its founding, its deities, its history.

Most of these records tell the official story. The sanitized version.

KAMI-LEGACY reads between the lines.

We use AI to extract what the official text doesn't say:
- Why is a deity of the *losers* enshrined in a *winner's* shrine?
- What bloodshed happened on this sacred ground?
- Why does this shrine exist exactly *here*, on this exact hill?

The result is a living database — built one shrine visit at a time — of Japan's true religious and political history.

---

## How it works

```
Visit a shrine
 ↓
Photograph the go-yuisho board (smartphone camera)
 ↓
GPS coordinates captured automatically
 ↓
Claude AI analyzes the image
 ├── 【陽の由緒】 Official history · enshrined deities
 ├── 【陰の真実】 War, plunder, power struggle — the dark side
 ├── 【神格の矛盾】 Why is THIS deity enshrined HERE? (the uncomfortable question)
 └── 【この地の因縁】 A message to whoever stands on this ground
 ↓
Saved to WordPress as draft (human reviews before publishing)
```

---

## The deeper question

When you stand at Kashima Jingu — a shrine to Takemikazuchi, the deity who *won* the transfer of the land — you also find Susanoo and Okuninushi enshrined there. The gods of the defeated.

Why?

Fear. Appeasement. Political absorption of local belief systems.

This pattern repeats across Japan. KAMI-LEGACY is mapping it.

---

## Tech stack

| Layer | Tech |
|-------|------|
| Frontend | PHP (intentionally simple — no framework) |
| AI | Claude API (claude-sonnet-4-6) |
| CMS | WordPress REST API |
| Server | Xserver |

API keys are stored server-side in `/home/achoo/.env_kami` (chmod 600). Never in code.

---

## Files

```
shot.php    # Everything — photo upload → AI analysis → WordPress save
```

One file. Intentional.

---

## Design philosophy

**Simple over clever.**
A shrine priest with basic PHP knowledge should be able to read this code.

**Human in the loop.**
AI output is always a WordPress *draft*. A human decides what gets published.

**Accumulation over perfection.**
One shrine visit = one record. 20 records = a pattern. 1,000 records = a map of how power and myth shaped this country.

---

## Roadmap

- [x] Photo → Claude analysis
- [x] GPS coordinate capture
- [x] Auto-detect shrine name from image
- [x] WordPress draft auto-save
- [x] 【神格の矛盾】 — detect when enemy deities are enshrined together
- [ ] English translation of analysis
- [ ] Public shrine map (GPS → shrine record)
- [ ] Community contributions
- [ ] Deity network visualization — who is enshrined with whom, and why

---

## For future developers

This project was started by one person visiting shrines in Ibaraki, Japan.

Before adding complexity, ask:
- Does this help someone standing in front of a shrine understand what they're looking at?
- Does this add to the database?

If not, it can wait.
