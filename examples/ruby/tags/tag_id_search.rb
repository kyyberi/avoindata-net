# -*- coding: utf-8 -*-
# 
# Esimerkki ruby koodin pätkä, jolla haetaan ja tuloste-
# taan id:llä haetun tagin kysymykset.
#
# Tekijä: Jarkko Moilanen (@kyyberi)
# Päivämäärä: 13.7.2013
# Lisenssi: http://creativecommons.org/licenses/by-sa/3.0/
# 
# -------------------------------------------------------

# Tarvittavat gemit, asenna tarvittaessa tyyliin: 
# "gem install json"
require 'open-uri'
require 'json'


# 1. Ensin haetaan ID tagien listauksesta

# URL mistä tag lista haetaan
@murl = 'http://api.avoindata.net/tags'
# hae JSON sisältö
@json_contents  = open(@murl) {|f| f.read }
# parsi JSON
@content = JSON.parse(@json_contents)
# ota toisen objektin id talteen
@needed_id = @content['tags'][1]['wordid']

# 2. haetaan kyseisen id:n omaavan tagin kysymykset

# URL mistä tag lista haetaan
@murl = 'http://api.avoindata.net/tags/id/' + @needed_id.to_s 
# hae JSON sisältö
@json_contents  = open(@murl) {|f| f.read }
# parsi JSON
@content = JSON.parse(@json_contents)

# iteroi tietojoukko
@content[@needed_id.to_s].each do |item|
  ititle = item['title']
  # tulosta tai mitä sitten haluatkaaan tehdä
  puts ititle
end
