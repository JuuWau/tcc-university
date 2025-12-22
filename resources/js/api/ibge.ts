import axios from 'axios';

export interface Uf {
  id: number;
  sigla: string;
  nome: string;
  regiao: {
    id: number;
    sigla: string;
    nome: string;
  };
}

export interface City {
  id: number;
  nome: string;
  microrregiao: {
    id: number;
    nome: string;
  };
}

const API_BASE = 'https://servicodados.ibge.gov.br/api/v1/localidades';

export const IbgeService = {
  async getUfData(): Promise<Uf[]> {
    try {
      const { data } = await axios.get<Uf[]>(`${API_BASE}/estados?orderBy=nome`);
      return data;
    } catch (error) {
      console.error('Erro ao buscar estados:', error);
      return [];
    }
  },

  async getCityData(uf: string): Promise<City[]> {
    try {
      const { data } = await axios.get<City[]>(`${API_BASE}/estados/${uf}/municipios`);
      return data;
    } catch (error) {
      console.error('Erro ao buscar municípios:', error);
      return [];
    }
  }
};
