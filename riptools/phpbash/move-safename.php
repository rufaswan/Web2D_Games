<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D Games.
    <https://github.com/rufaswan/Web2D_Games>

Web2D Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
declare( strict_types=1 );

require 'tool.inc';
tool::require('func-sh');

function movesafe( string $ent ) : void
{
	if ( ! is_file($ent) )
		return;

	$pwd  = sh::pwd();
	$real = realpath($ent);
	//tool::trace('REAL', $real);

	$dir  =  dirname($real);
	$base = basename($real);
	sh::cd($dir);

	$safe = tool::safepath($base, 15, 0x40);
	//tool::trace('SAFE', $safe);
	if (   empty($safe)  )  return;
	if ( is_file($safe)  )  return;
	if ( $base === $safe )  return;

	tool::trace('[MOVE]', $base, $safe);
	//tool::move($base, $safe);
	rename($base, $safe);

	sh::cd($pwd);
}

for ( $i=1; $i < $argc; $i++ )
	movesafe( $argv[$i] );

/*
You are exactly right that the majority of common English words are short, and very few everyday words exceed 8 letters.

## Word Length Breakdown

In everyday speech and writing, 70% to 80% of all words used are 1 to 5 characters long.

Here is how the lengths of the most common 1,000 words in English break down:

* 1–3 characters: 35% of common words (e.g., the, and, it, but, for, you).
* 4–5 characters: 38% of common words (e.g., with, they, this, about, would).
* 6–7 characters: 18% of common words (e.g., people, should, before, through).
* 8 characters: Only about 5% of common words (e.g., together, business, children).
* 9+ characters: Less than 4% of common words (e.g., government, different). [1]

## Why Common Words Are Short

Languages naturally optimize for efficiency through a concept known as Zipf's Law or the law of abbreviation.

* Humans subconsciously shorten words they use most frequently to save time and breath.
* Long words (9+ letters) are usually reserved for specific technical, scientific, or formal concepts.

If you'd like, let me know:

* Are you designing a user interface (UI) or database that needs a specific text field size?
* Are you creating a word game or puzzle?
* Do you need to find the most common 7 or 8-letter words for a project?

I can give you exact letter counts or word lists tailored to your needs.

[1] [https://www.reddit.com](https://www.reddit.com/r/ENGLISH/comments/1bz2ipq/whats_it_called_when_words_are_omitted_to_shorten/)

# ---

The maximum length for a common, modern English word is 15 characters. [1]

Words longer than 15 letters almost always turn into highly specialized scientific terms, medical conditions, or outdated jargon that people do not use in daily life. [2]

## The Longest Common Modern Words

These are the longest words that an average English speaker reads, writes, and says today without them feeling forced or outdated:

* 15 Characters
* Characteristics
   * Professionalism
   * Environmentally
   * Responsibilities
* 14 Characters
* Identification
   * Recommendation
   * Organizational
   * Representation
* 13 Characters
* Communication
   * Understanding
   * International
   * Manufacturing

## Why the Cutoff is 15 Letters

Words stop growing past 15 characters because of how modern English is structured:

* Prefix + Root + Suffix: Long modern words are just short words with pieces added to the front and back (e.g., Un-character-ist-ic-al-ly).
* The 15-Letter Limit: Once a word hits 15 letters, humans naturally look for shortcuts. We either swap it for a shorter word or turn it into an acronym (like changing Information Technology to IT).

If you want to look at more specific types of words, let me know:

* Do you want to see the longest common words for a specific field like business or technology?
* Are you looking for the longest common words that do not use prefixes or suffixes?
* Do you need this to set a maximum character limit for a web form or database?

[1] [https://office-watch.com](https://office-watch.com/2016/office-for-mac-password-limitations/)
[2] [https://www.youtube.com](https://www.youtube.com/watch?v=3Y5sGb0E5fw)
*/
