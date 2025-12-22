interface ViaCepResponse {
    cep: string
    logradouro: string
    complemento: string
    bairro: string
    localidade: string
    uf: string
    ibge: string
    gia: string
    ddd: string
    siafi: string
    erro?: boolean
}

export const ViaCep = () => {
    async function getCepData(cep: string): Promise<ViaCepResponse | null> {
        try {
            const cepTreated = cep.replace(/\D/g, '');
            const response = await fetch(`https://viacep.com.br/ws/${cepTreated}/json/`);
            const data: ViaCepResponse = await response.json();

            if (data.erro) {
                console.error('CEP não encontrado');
                return null;
            }

            return data;
        } catch (error) {
            console.error(error);
            return null;
        }
    }

    return {
        getCepData
    };
};
