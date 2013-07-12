# -*- coding: utf-8 -*-
# 
# Esimerkki ruby koodin pätkä, jolla haetaan ja tuloste-
# taan kategorioiden nimet ja kysymysmäärät 
# avoindata.net palvelun API:n kautta. 
#
# Tekijä: Jarkko Moilanen (@kyyberi)
# Päivämäärä: 11.7.2013
# Lisenssi: http://creativecommons.org/licenses/by-sa/3.0/
# 
# -------------------------------------------------------

# Tarvittavat gemit, asenna tarvittaessa tyyliin: 
# "gem install json"
require 'open-uri'
require 'json'

# URL mistä JSON haetaan
@murl = 'http://api.avoindata.net/categories'

# hae JSON sisältö
@json_contents  = open(@murl) {|f| f.read }

# parsi JSON
@content = JSON.parse(@json_contents)

# iteroi tietojoukko
@content['categories'].each do |item|
  ilkm  = item['count'].to_s
  ititle = item['title']
  # tulosta tai mitä sitten haluatkaaan tehdä
  puts ititle
  puts ilkm
end
